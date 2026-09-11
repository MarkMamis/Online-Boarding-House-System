<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\ChatbotConversation;
use App\Models\ChatbotMessage;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\AI\AiToolExecutor;
use App\Services\AI\AiToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotAiAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.huggingface.token' => 'test-hf-token',
            'services.huggingface.model' => 'deepseek-ai/DeepSeek-V4.1-Flash:novita',
            'services.huggingface.endpoint' => 'https://router.huggingface.co/v1/chat/completions',
            'chatbot.rate_limit_per_minute' => 20,
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
        ], $attributes));
    }

    public function test_chatbot_history_returns_existing_conversation_and_messages(): void
    {
        $user = $this->createCompleteStudent();

        $conversation = ChatbotConversation::create([
            'user_id' => $user->id,
            'role' => 'student',
        ]);

        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello assistant',
        ]);

        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Hello! How can I help you with OBHS today?',
            'meta' => ['action' => ['label' => 'Browse rooms', 'url' => '/student/rooms']],
        ]);

        $response = $this->actingAs($user)->getJson('/chatbot/history');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'conversation_id',
            'messages' => [
                '*' => ['id', 'role', 'content', 'meta', 'created_at'],
            ],
        ]);
        $response->assertJsonPath('conversation_id', $conversation->id);
        $this->assertCount(2, $response->json('messages'));
    }

    public function test_ai_assistant_direct_response_without_tools(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'name' => 'AdminDev',
            'full_name' => 'Admin Developer',
            'email_verified_at' => now(),
        ]);

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Your name is Admin Developer and you are logged in as an administrator.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson('/chatbot/message', [
            'content' => 'What is my name and role?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'Your name is Admin Developer and you are logged in as an administrator.',
            'meta' => [],
        ]);

        // Assert messages stored in database
        $this->assertDatabaseHas('chatbot_messages', [
            'role' => 'user',
            'content' => 'What is my name and role?',
        ]);

        $this->assertDatabaseHas('chatbot_messages', [
            'role' => 'assistant',
            'content' => 'Your name is Admin Developer and you are logged in as an administrator.',
        ]);
    }

    public function test_ai_assistant_executes_tool_call_for_database_query(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create 3 students and 2 landlords
        User::factory()->count(3)->create(['role' => 'student', 'is_active' => true]);
        User::factory()->count(2)->create(['role' => 'landlord', 'is_active' => true]);

        $callCount = 0;
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function ($request) use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    // First call: model decides to call count_students tool
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'role' => 'assistant',
                                    'content' => '',
                                    'tool_calls' => [
                                        [
                                            'id' => 'call_123',
                                            'type' => 'function',
                                            'function' => [
                                                'name' => 'count_students',
                                                'arguments' => json_encode(['status' => 'active']),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ], 200);
                }

                // Second call: model receives tool result and answers naturally
                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Currently, there are 3 registered active students in the system.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Ilan ang students?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'Currently, there are 3 registered active students in the system.',
        ]);

        $this->assertEquals(2, $callCount);
    }

    public function test_role_authorization_rejects_unauthorized_tool(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
            'school_id_verification_status' => 'approved',
        ]);

        $registry = app(AiToolRegistry::class);
        $executor = app(AiToolExecutor::class);

        // Student should not be authorized to call count_users or count_landlords
        $this->assertFalse($registry->isAuthorized('count_users', 'student'));
        $this->assertFalse($registry->isAuthorized('count_landlords', 'student'));

        // If an unauthorized execution is attempted:
        $result = $executor->execute('count_users', ['role' => 'student'], $student);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Unauthorized', $result['error']);
    }

    public function test_student_can_query_own_booking_status(): void
    {
        $student = $this->createCompleteStudent();

        $landlord = User::factory()->create(['role' => 'landlord']);
        $property = Property::factory()->create([
            'landlord_id' => $landlord->id,
            'name' => 'Greenview Dormitory',
            'approval_status' => 'approved',
        ]);

        $room = Room::factory()->create([
            'property_id' => $property->id,
            'room_number' => '101',
            'price' => 3500,
        ]);

        Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'status' => 'approved',
            'check_in' => now()->subDays(5)->toDateString(),
            'check_out' => now()->addDays(25)->toDateString(),
            'monthly_rent_amount' => 3500,
        ]);

        $callCount = 0;
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'role' => 'assistant',
                                    'content' => '',
                                    'tool_calls' => [
                                        [
                                            'id' => 'call_booking_456',
                                            'type' => 'function',
                                            'function' => [
                                                'name' => 'get_my_booking',
                                                'arguments' => '{}',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ], 200);
                }

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Your booking is currently active in Greenview Dormitory, Room 101 with a monthly rent of ₱3,500.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($student)->postJson('/chatbot/message', [
            'content' => 'What is my booking status?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'Your booking is currently active in Greenview Dormitory, Room 101 with a monthly rent of ₱3,500.',
        ]);
        $this->assertEquals('/student/requests', $response->json('meta.action.url'));
    }

    public function test_ai_outage_graceful_handling(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'error' => 'Model overloaded',
            ], 503),
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Hello',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'Sorry, I could not reach the assistant right now. Please try again shortly.',
            'meta' => [],
        ]);
    }

    public function test_structured_json_tool_fallback_support(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::factory()->count(4)->create(['role' => 'student', 'is_active' => true]);

        $callCount = 0;
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function () use (&$callCount) {
                $callCount++;
                if ($callCount === 1) {
                    // LLM outputs JSON tool call inside text content instead of tool_calls array
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'role' => 'assistant',
                                    'content' => json_encode([
                                        'type' => 'tool_call',
                                        'tool' => 'count_students',
                                        'arguments' => ['status' => 'all'],
                                    ]),
                                ],
                            ],
                        ],
                    ], 200);
                }

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'There are 4 students registered in the system.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'How many students do we have?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'There are 4 students registered in the system.',
        ]);
        $this->assertEquals(2, $callCount);
    }

    public function test_landlord_queries_own_properties(): void
    {
        $landlord1 = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);
        $landlord2 = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        Property::factory()->create([
            'landlord_id' => $landlord1->id,
            'name' => 'Landlord One Boarding House',
        ]);
        Property::factory()->create([
            'landlord_id' => $landlord2->id,
            'name' => 'Landlord Two Boarding House',
        ]);

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function ($request) {
                $payload = $request->data();
                $messages = $payload['messages'];
                $lastMsg = end($messages);

                if ($lastMsg['role'] === 'user' && str_contains($lastMsg['content'], 'properties do I have')) {
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'role' => 'assistant',
                                    'content' => '',
                                    'tool_calls' => [
                                        [
                                            'id' => 'call_prop_1',
                                            'type' => 'function',
                                            'function' => [
                                                'name' => 'get_landlord_properties',
                                                'arguments' => '{}',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ], 200);
                }

                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'You currently own 1 property: Landlord One Boarding House.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($landlord1)->postJson('/chatbot/message', [
            'content' => 'How many properties do I have?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'reply' => 'You currently own 1 property: Landlord One Boarding House.',
        ]);
    }

    public function test_conversation_memory_is_sent_to_huggingface(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $conversation = ChatbotConversation::create([
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);

        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'I am looking into occupancy.',
        ]);

        ChatbotMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => 'Sure! What would you like to know about occupancy?',
        ]);

        $capturedMessages = [];
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function ($request) use (&$capturedMessages) {
                $capturedMessages = $request->data()['messages'];
                return Http::response([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Occupancy statistics are updated.',
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Which one has the highest?',
        ]);

        $response->assertStatus(200);

        // Verify history turns were sent
        $historyContents = array_column($capturedMessages, 'content');
        $this->assertContains('I am looking into occupancy.', $historyContents);
        $this->assertContains('Sure! What would you like to know about occupancy?', $historyContents);
        $this->assertContains('Which one has the highest?', $historyContents);
    }

    public function test_chatbot_rate_limiting_enforced(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Hello!']],
                ],
            ], 200),
        ]);

        // Send 20 allowed requests
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($admin)->postJson('/chatbot/message', [
                'content' => "Test message {$i}",
            ]);
            $response->assertStatus(200);
        }

        // The 21st request should be throttled
        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Rate limit exceeded message',
        ]);

        $response->assertStatus(429);
    }

    public function test_ai_response_converts_internal_routes_to_structured_actions_and_labels(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $aiContent = "Mayroong **16 na registered students** sa OBHS.\n\n" .
            "Narito ang breakdown:\n" .
            "- **Verified students:** 4\n" .
            "- **Pending verification:** 3\n\n" .
            "Maaari mong bisitahin ang Student Verifications sa `/admin/student-verifications`.\n\n" .
            "May iba ka pa bang gustong malaman? 😊";

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $aiContent,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Ilan ang registered students?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reply',
            'message',
            'actions' => [
                '*' => ['type', 'label', 'route', 'url'],
            ],
            'meta',
        ]);

        // Raw technical path should be removed from prose
        $this->assertStringNotContainsString('/admin/student-verifications', $response->json('reply'));

        // Structured action should be generated with friendly label
        $actions = $response->json('actions');
        $this->assertNotEmpty($actions);
        $this->assertEquals('Review Students', $actions[0]['label']);
        $this->assertEquals('/admin/student-verifications', $actions[0]['route']);
        $this->assertEquals('/admin/student-verifications', $actions[0]['url']);
    }

    public function test_unauthorized_internal_route_in_ai_response_is_rejected(): void
    {
        $student = $this->createCompleteStudent();

        // Assistant accidentally mentions an admin route to a student
        $aiContent = "You can view the system users at `/admin/users`.";

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $aiContent,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($student)->postJson('/chatbot/message', [
            'content' => 'Show me users',
        ]);

        $response->assertStatus(200);
        // Student must NOT receive an admin action button
        $actions = $response->json('actions');
        $this->assertEmpty($actions);
    }

    public function test_multiple_action_buttons_for_multi_concern_overview(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $aiContent = "Narito ang buod ng mga pending concerns:\n\n" .
            "- **3 pending student verifications** sa `/admin/student-verifications`\n" .
            "- **1 pending landlord verification** sa `/admin/landlord-verifications`\n" .
            "- **6 unresolved reports** sa `/admin/reports`";

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => $aiContent,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Give me an overview of pending concerns',
        ]);

        $response->assertStatus(200);
        $actions = $response->json('actions');
        $this->assertCount(3, $actions);

        $labels = array_column($actions, 'label');
        $this->assertContains('Review Students', $labels);
        $this->assertContains('Review Landlords', $labels);
        $this->assertContains('View Reports', $labels);

        $routes = array_column($actions, 'route');
        $this->assertContains('/admin/student-verifications', $routes);
        $this->assertContains('/admin/landlord-verifications', $routes);
        $this->assertContains('/admin/reports', $routes);
    }
}


