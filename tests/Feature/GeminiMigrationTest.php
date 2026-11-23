<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomFeedback;
use App\Models\TenantOnboarding;
use App\Models\User;
use App\Services\AI\AiAssistantService;
use App\Services\AI\AiToolExecutor;
use App\Services\AI\AiToolRegistry;
use App\Services\GeminiService;
use App\Services\ReportPriorityClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.api_key' => 'test-gemini-key',
            'services.gemini.chat_model' => 'gemini-3.8-flash',
            'services.gemini.classification_model' => 'gemini-3.5-flash-lite',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'chatbot.rate_limit_per_minute' => 60,
        ]);
    }

    protected function createCompleteStudent(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'full_name' => 'Juan Dela Cruz',
            'email_verified_at' => now(),
            'profile_image_path' => 'profiles/test.jpg',
            'contact_number' => '09123456789',
            'birth_date' => '2004-01-01',
            'college' => 'College of Science',
            'program' => 'BS Computer Science',
            'year_level' => '1st Year',
            'gender' => 'Male',
            'address' => 'Sample Address',
            'emergency_contact_name' => 'Maria Dela Cruz',
            'emergency_contact_number' => '09123456780',
            'emergency_contact_relationship' => 'Mother',
            'parent_contact_name' => 'Pedro Dela Cruz',
            'parent_contact_number' => '09123456781',
            'parent_contact_address' => 'Sample Parent Address',
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => 'enrollments/test.pdf',
            'school_id_verification_status' => 'approved',
            'onboarding_complete' => true,
        ], $attributes));
    }

    protected function createCompleteLandlord(array $attributes = []): User
    {
        $landlord = User::factory()->create(array_merge([
            'role' => 'landlord',
            'full_name' => 'Test Landlord',
            'email_verified_at' => now(),
            'contact_number' => '09123456789',
            'boarding_house_name' => 'Sunshine Dorm',
            'onboarding_complete' => true,
        ], $attributes));

        \App\Models\LandlordProfile::create([
            'user_id' => $landlord->id,
            'about' => 'Reliable and accommodating landlord.',
        ]);

        return $landlord;
    }

    protected function createVerifiedStudentWithBooking(Property $property, Room $room): array
    {
        $student = $this->createCompleteStudent();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'status' => 'approved',
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->addDays(25)->toDateString(),
            'monthly_rent_amount' => 3000,
        ]);

        TenantOnboarding::create([
            'booking_id' => $booking->id,
            'tenant_id' => $student->id,
            'landlord_id' => $property->landlord_id,
            'status' => 'completed',
        ]);

        return [$student, $booking];
    }

    // ==========================================
    // 1. Sentiment Analysis & Retry Tests
    // ==========================================

    public function test_room_feedback_sentiment_classification_and_null_score(): void
    {
        $landlord = $this->createCompleteLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['property_id' => $property->id]);

        [$student] = $this->createVerifiedStudentWithBooking($property, $room);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode(['label' => 'positive'])],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($student)->post(route('student.rooms.feedback', $room->id), [
            'rating' => 5,
            'comment' => 'This room is clean, secure, and the landlord is very accommodating!',
        ]);

        $response->assertRedirect(route('student.rooms.feedback_page', $room->id));

        $feedback = RoomFeedback::where('room_id', $room->id)->first();
        $this->assertNotNull($feedback);
        $this->assertEquals('positive', $feedback->sentiment_label);
        $this->assertNull($feedback->sentiment_score, 'sentiment_score must remain null without fabricated probabilities');
    }

    public function test_room_feedback_blank_comment_skips_gemini_call(): void
    {
        $landlord = $this->createCompleteLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['property_id' => $property->id]);

        [$student] = $this->createVerifiedStudentWithBooking($property, $room);

        Http::fake();

        $response = $this->actingAs($student)->post(route('student.rooms.feedback', $room->id), [
            'rating' => 4,
            'comment' => '   ',
        ]);

        $response->assertRedirect(route('student.rooms.feedback_page', $room->id));

        $feedback = RoomFeedback::where('room_id', $room->id)->first();
        $this->assertNotNull($feedback);
        $this->assertNull($feedback->sentiment_label);
        $this->assertNull($feedback->sentiment_score);

        Http::assertNothingSent();
    }

    public function test_failed_analysis_saves_feedback_with_null_sentiment_and_allows_landlord_retry(): void
    {
        $landlord = $this->createCompleteLandlord();
        $property = Property::factory()->create(['landlord_id' => $landlord->id]);
        $room = Room::factory()->create(['property_id' => $property->id]);

        [$student] = $this->createVerifiedStudentWithBooking($property, $room);

        $attemptCount = 0;
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => function () use (&$attemptCount) {
                $attemptCount++;
                // First round: initial submission fails with 500
                if ($attemptCount <= 2) {
                    return Http::response(['error' => 'Server Error'], 500);
                }
                // Second round: landlord re-analysis succeeds
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => json_encode(['label' => 'negative'])],
                                ],
                                'role' => 'model',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($student)->post(route('student.rooms.feedback', $room->id), [
            'rating' => 1,
            'comment' => 'The water is always out and the room is noisy.',
        ]);

        $response->assertRedirect(route('student.rooms.feedback_page', $room->id));

        $feedback = RoomFeedback::where('room_id', $room->id)->first();
        $this->assertNotNull($feedback);
        $this->assertNull($feedback->sentiment_label, 'Failed analysis must leave sentiment_label null');
        $this->assertNull($feedback->sentiment_score);

        // Later, landlord triggers re-analysis via analyzePending
        $retryResponse = $this->actingAs($landlord)->post(route('landlord.feedback.analyze'));
        $retryResponse->assertRedirect();
        $retryResponse->assertSessionHas('status', 'Sentiment analysis complete: 1 updated.');

        $feedback->refresh();
        $this->assertEquals('negative', $feedback->sentiment_label);
        $this->assertNull($feedback->sentiment_score);
    }

    // ==========================================
    // 2. Report Priority Classification & Fallback
    // ==========================================

    public function test_report_priority_classifier_uses_gemini_structured_output(): void
    {
        $classifier = app(ReportPriorityClassifier::class);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode(['label' => 'high'])],
                            ],
                            'role' => 'model',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $priority = $classifier->classify('Power outage', 'There are exposed live wires sparking in the corridor.');
        $this->assertEquals('high', $priority);
    }

    public function test_report_priority_classifier_falls_back_to_heuristic_on_quota_exceeded(): void
    {
        $classifier = app(ReportPriorityClassifier::class);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 429,
                    'message' => 'Resource has been exhausted (e.g. check quota).',
                    'status' => 'RESOURCE_EXHAUSTED',
                ],
            ], 429),
        ]);

        // Emergency text containing high-severity keywords should fall back to heuristic 'high'
        $priority = $classifier->classify('Emergency', 'Fire and smoke detected on the second floor!');
        $this->assertEquals('high', $priority);

        // Minor text should fall back to 'medium' or 'low'
        $priorityMinor = $classifier->classify('Faucet leak', 'The bathroom faucet has a slow minor drip.');
        $this->assertContains($priorityMinor, ['low', 'medium']);
    }

    public function test_report_priority_classifier_heuristic_when_gemini_not_configured(): void
    {
        config(['services.gemini.api_key' => null]);
        $classifier = app(ReportPriorityClassifier::class);

        Http::fake();

        $priority = $classifier->classify('Theft report', 'Someone broke into the room and stole a laptop.');
        $this->assertEquals('high', $priority);

        Http::assertNothingSent();
    }

    // ==========================================
    // 3. Chatbot Tool-Call Round Trips & Role Isolation
    // ==========================================

    public function test_chatbot_executes_gemini_function_call_and_returns_second_turn_reply(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $callCount = 0;
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => function ($request) use (&$callCount) {
                $callCount++;
                $data = $request->data();

                if ($callCount === 1) {
                    // First turn: Verify Gemini request format
                    $this->assertArrayHasKey('contents', $data);
                    $this->assertArrayHasKey('systemInstruction', $data);
                    $this->assertArrayHasKey('tools', $data);
                    $this->assertNotEmpty($data['tools'][0]['functionDeclarations']);

                    // Model requests tool call
                    return Http::response([
                        'candidates' => [
                            [
                                'content' => [
                                    'role' => 'model',
                                    'parts' => [
                                        [
                                            'functionCall' => [
                                                'name' => 'count_users',
                                                'args' => ['role' => 'landlord'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ], 200);
                }

                // Second turn: Verify tool response was fed back in Gemini format
                $this->assertArrayHasKey('contents', $data);
                $contents = $data['contents'];
                $lastMessage = end($contents);
                $this->assertEquals('function', $lastMessage['role']);
                $this->assertEquals('count_users', $lastMessage['parts'][0]['functionResponse']['name']);

                // Final answer
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'role' => 'model',
                                'parts' => [
                                    ['text' => 'There are currently 5 registered landlords in the system.'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'How many landlords are registered?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'There are currently 5 registered landlords in the system.',
        ]);
        $this->assertEquals(2, $callCount);
    }

    public function test_chatbot_role_isolation_restricts_student_from_admin_tools(): void
    {
        $student = $this->createCompleteStudent();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => function ($request) {
                $data = $request->data();
                $declarations = $data['tools'][0]['functionDeclarations'] ?? [];
                $declarationNames = array_column($declarations, 'name');

                // Student must NOT have access to admin inspection or count tools
                $this->assertNotContains('count_users', $declarationNames);
                $this->assertNotContains('count_landlords', $declarationNames);
                $this->assertNotContains('inspect_user', $declarationNames);

                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'role' => 'model',
                                'parts' => [
                                    ['text' => 'I can help you search for rooms or check your booking.'],
                                ],
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($student)->postJson('/chatbot/message', [
            'content' => 'Show me all system users',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'I can help you search for rooms or check your booking.',
        ]);
    }

    // ==========================================
    // 4. Quota / Provider Failures & Frontend Compatibility
    // ==========================================

    public function test_chatbot_handles_gemini_quota_exhaustion_cleanly(): void
    {
        $student = $this->createCompleteStudent();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 429,
                    'message' => 'Resource has been exhausted (quota limit reached).',
                ],
            ], 429),
        ]);

        $response = $this->actingAs($student)->postJson('/chatbot/message', [
            'content' => 'Are there available rooms?',
        ]);

        $response->assertStatus(200);
        $json = $response->json();

        // Response contract maintained: reply, actions, meta
        $this->assertArrayHasKey('reply', $json);
        $this->assertArrayHasKey('actions', $json);
        $this->assertArrayHasKey('meta', $json);
        $this->assertStringContainsString('shortly', $json['reply']);
        // Must never leak the API key or raw provider exception to the user
        $this->assertStringNotContainsString('test-gemini-key', json_encode($json));
        $this->assertStringNotContainsString('RESOURCE_EXHAUSTED', json_encode($json));
    }

    public function test_chatbot_unconfigured_api_key_returns_friendly_message(): void
    {
        config(['services.gemini.api_key' => null]);

        $student = $this->createCompleteStudent();

        Http::fake();

        $response = $this->actingAs($student)->postJson('/chatbot/message', [
            'content' => 'Hello',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'The assistant is temporarily offline for maintenance. Please check your configuration.',
        ]);

        Http::assertNothingSent();
    }

    public function test_authenticated_chatbot_history_returns_clean_parseable_json(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/chatbot/history', [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'application/json'));

        $content = $response->getContent();
        // The raw string must start with JSON '{' without any PHP deprecation notice prepended
        $this->assertStringStartsWith('{', trim($content), 'JSON response must not have deprecation warnings prepended.');
        $decoded = json_decode($content, true);
        $this->assertNotNull($decoded, 'Response body must be strictly valid parseable JSON.');
        $this->assertArrayHasKey('conversation_id', $decoded);
        $this->assertArrayHasKey('messages', $decoded);
    }

    public function test_admin_dashboard_stats_returns_clean_parseable_json(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard.stats', ['days' => 14]), [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringStartsWith('{', trim($content), 'Stats JSON response must not have deprecation warnings prepended.');
        $decoded = json_decode($content, true);
        $this->assertNotNull($decoded, 'Stats response body must be strictly valid parseable JSON.');
        $this->assertArrayHasKey('labels', $decoded);
        $this->assertArrayHasKey('registrations', $decoded);
    }
}
