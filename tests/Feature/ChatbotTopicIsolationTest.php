<?php

namespace Tests\Feature;

use App\Models\ChatbotConversation;
use App\Models\Report;
use App\Models\User;
use App\Services\AI\AiAssistantService;
use App\Services\AI\AiPromptBuilder;
use App\Services\AI\AiToolRegistry;
use App\Services\AI\Tools\StudentTools;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTopicIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected string $tz;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tz = config('app.timezone') ?: env('APP_TIMEZONE', 'Asia/Manila');
        Carbon::setTestNow(Carbon::create(2026, 9, 11, 10, 0, 0, $this->tz));

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'AdminDev',
            'full_name' => 'Admin Developer',
            'email' => 'admin@example.com',
            'contact_number' => '09171234567',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Juan Student',
            'email' => 'juan@example.com',
            'contact_number' => '09181234567',
            'created_at' => now()->subDays(30),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_student_statistics_with_date_filtering(): void
    {
        // 2 students registered today
        User::factory()->create([
            'role' => 'student',
            'name' => 'Today Student 1',
            'created_at' => now(),
            'school_id_verification_status' => 'pending',
            'contact_number' => '09181111111',
        ]);
        User::factory()->create([
            'role' => 'student',
            'name' => 'Today Student 2',
            'created_at' => now()->subHour(),
            'school_id_verification_status' => 'approved',
            'contact_number' => '09182222222',
        ]);

        // 1 student registered yesterday
        User::factory()->create([
            'role' => 'student',
            'name' => 'Yesterday Student',
            'created_at' => now()->subDay(),
            'school_id_verification_status' => 'approved',
            'contact_number' => '09183333333',
        ]);

        $tools = app(StudentTools::class);
        $result = $tools->getStudentStatistics($this->admin, ['period' => 'today']);

        $this->assertTrue($result['period']['is_filtered']);
        $this->assertEquals('today', $result['period']['label']);
        $this->assertEquals(2, $result['new_students']);
        $this->assertEquals(2, $result['total_registered_in_period']);
        $this->assertEquals(1, $result['verified_student_ids']);
        $this->assertEquals(1, $result['pending_verifications']);
        $this->assertEquals('/admin/student-verifications', $result['action_url']);
        $this->assertEquals('Review Students', $result['action_label']);
    }

    public function test_get_recent_students_listing(): void
    {
        User::factory()->create([
            'role' => 'student',
            'name' => 'Maria Clara',
            'full_name' => 'Maria Clara',
            'created_at' => now(),
            'contact_number' => '09184444444',
        ]);

        $tools = app(StudentTools::class);
        $result = $tools->getRecentStudents($this->admin, ['period' => 'today']);

        $this->assertEquals(1, $result['total_students']);
        $this->assertEquals('Maria Clara', $result['students'][0]['name']);
        $this->assertEquals('/admin/student-verifications', $result['action_url']);
    }

    public function test_prompt_builder_contains_authoritative_user_identity_and_topic_isolation(): void
    {
        $promptBuilder = app(AiPromptBuilder::class);
        $prompt = $promptBuilder->buildSystemPrompt($this->admin);

        $this->assertStringContainsString('AUTHORITATIVE USER IDENTITY:', $prompt);
        $this->assertStringContainsString('Current-Turn Priority & Topic Isolation:', $prompt);
        $this->assertStringContainsString('Admin: Oversees all system users', $prompt);
        $this->assertStringContainsString('Admins CANNOT create, edit, price, or operate rooms', $prompt);
        $this->assertStringContainsString('Grounding in Real Data & No Unsupported Inferences:', $prompt);
        $this->assertStringContainsString('Pending student verification" does NOT mean registered today', $prompt);
    }

    public function test_admin_is_authorized_for_student_and_report_tools(): void
    {
        $registry = app(AiToolRegistry::class);

        $this->assertTrue($registry->isAuthorized('count_students', 'admin'));
        $this->assertTrue($registry->isAuthorized('get_student_statistics', 'admin'));
        $this->assertTrue($registry->isAuthorized('get_recent_students', 'admin'));
        $this->assertTrue($registry->isAuthorized('get_report_statistics', 'admin'));
    }

    public function test_sequence_c_topic_transition_reports_to_new_students(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Admin Conversation',
        ]);

        // Prior turn in history about reports
        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'ilan reports today?',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'Walang bagong report na na-submit today.',
            'meta' => [
                'action' => [
                    'type' => 'internal_route',
                    'label' => 'View Reports',
                    'route' => '/admin/reports',
                ],
            ],
        ]);

        // Create 2 students registered today
        User::factory()->create([
            'role' => 'student',
            'name' => 'New Student 1',
            'created_at' => now(),
            'contact_number' => '09185555551',
        ]);
        User::factory()->create([
            'role' => 'student',
            'name' => 'New Student 2',
            'created_at' => now(),
            'contact_number' => '09185555552',
        ]);

        // Mock HF: Turn calls get_student_statistics with period='today', then answers cleanly
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_student_stats_1',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'get_student_statistics',
                                            'arguments' => json_encode(['period' => 'today']),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => "May **2 new student registrations today**.\n\nPwede mong i-review ang kanilang mga dokumento sa Student Verifications.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'may new students ba today?');

        // Verify response focuses strictly on students, without mentioning old reports or rooms
        $this->assertStringContainsString('2 new student registrations today', $response['reply']);
        $this->assertStringNotContainsString('report', strtolower($response['reply']));
        $this->assertStringNotContainsString('room', strtolower($response['reply']));

        // Verify action button generated is Review Students, NOT View Reports
        $this->assertNotEmpty($response['meta']['actions']);
        $this->assertEquals('/admin/student-verifications', $response['meta']['actions'][0]['route']);
        $this->assertEquals('Review Students', $response['meta']['actions'][0]['label']);
    }

    public function test_sequence_b_follow_up_preserves_date_context(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Follow up context session',
        ]);

        // Seed 5 reports today, 3 high priority
        for ($i = 0; $i < 3; $i++) {
            Report::create([
                'user_id' => $this->student->id,
                'title' => "High Issue {$i}",
                'description' => 'Description',
                'status' => 'pending',
                'priority' => 'high',
                'created_at' => now(),
            ]);
        }
        for ($i = 0; $i < 2; $i++) {
            Report::create([
                'user_id' => $this->student->id,
                'title' => "Low Issue {$i}",
                'description' => 'Description',
                'status' => 'pending',
                'priority' => 'low',
                'created_at' => now(),
            ]);
        }

        // History: User asked "ilan reports today?", Assistant replied "May 5 reports today."
        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'ilan reports today?',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'May 5 reports na na-submit today.',
        ]);

        // User follows up: "ilan dun ang high?"
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_report_follow_up',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'get_report_statistics',
                                            'arguments' => json_encode(['period' => 'today', 'priority' => 'high']),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => "**3 sa 5 reports today** ang High Priority.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'ilan dun ang high?');

        $this->assertStringContainsString('3 sa 5 reports today', $response['reply']);
        $this->assertEquals('/admin/reports', $response['meta']['actions'][0]['route']);
    }

    public function test_sequence_a_operating_rooms_then_unrelated_reports_question(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Admin Operating Rooms Transition',
        ]);

        // Prior turn in history: admin asked where to operate rooms
        $conversation->messages()->create([
            'role' => 'user',
            'content' => 'where can I operate my rooms?',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => 'As an administrator, you can monitor properties and room listings from Property Approvals, but creating and managing rooms belongs strictly to landlords.',
        ]);

        // Next turn: admin asks "ilan reports today?" (unrelated new topic)
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_report_stats_a',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'get_report_statistics',
                                            'arguments' => json_encode(['period' => 'today']),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => "Walang bagong report na na-submit today.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'ilan reports today?');

        // Verify response contains only reports answer, and zero room-management text
        $this->assertStringContainsString('Walang bagong report na na-submit today', $response['reply']);
        $this->assertStringNotContainsString('room', strtolower($response['reply']));
        $this->assertStringNotContainsString('properties', strtolower($response['reply']));
    }

    public function test_sequence_e_user_asks_name_answered_directly_from_context(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'User Identity Check',
        ]);

        // The AI does not need tool calls for identity; it answers directly using system prompt context
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => "Your account name is **AdminDev** (Admin Developer) and your role is **admin**.",
                        ],
                    ],
                ],
            ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'what is my name?');

        $this->assertStringContainsString('AdminDev', $response['reply']);
    }

    public function test_sequence_f_admin_queries_student_count_without_role_rejection(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Admin Query Students',
        ]);

        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => null,
                                'tool_calls' => [
                                    [
                                        'id' => 'call_count_students_f',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'count_students',
                                            'arguments' => json_encode(['status' => 'all']),
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ], 200)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => "May **1 registered student** sa OBHS.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'ilan students?');

        $this->assertStringContainsString('registered student', $response['reply']);
        $this->assertStringNotContainsString('outside your role', strtolower($response['reply']));
    }

    public function test_transient_http_failure_retries_and_recovers(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Retry Test',
        ]);

        // First attempt fails with 503, second attempt succeeds with 200
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::sequence()
                ->push(['error' => 'Service Unavailable'], 503)
                ->push([
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => 'Recovered successfully on retry.',
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'Hello');

        $this->assertEquals('Recovered successfully on retry.', $response['reply']);
    }

    public function test_client_error_does_not_retry_unnecessarily(): void
    {
        config(['services.huggingface.token' => 'invalid-token']);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Auth Error Test',
        ]);

        // 401 unauthorized: should not retry
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'Hello');

        $this->assertStringContainsString('Sorry, I could not reach the assistant right now', $response['reply']);
        Http::assertSentCount(1);
    }
}
