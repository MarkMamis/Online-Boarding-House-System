<?php

namespace Tests\Feature;

use App\Models\ChatbotConversation;
use App\Models\User;
use App\Services\AI\AiAssistantService;
use App\Services\AI\AiPromptBuilder;
use App\Services\AI\AiToolExecutor;
use App\Services\AI\AiToolRegistry;
use App\Services\AI\Tools\StudentTools;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotStudentAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected User $landlord;
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
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Juan Student',
            'email' => 'juan@example.com',
            'created_at' => Carbon::create(2025, 12, 1),
            'school_id_verification_status' => 'pending',
            'college' => null,
            'program' => null,
        ]);

        $this->landlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Pedro Landlord',
            'email' => 'pedro@example.com',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_student_statistics_grouped_by_college_with_reconciliation(): void
    {
        // 4 CAS, 2 CCS, 1 CTE, 3 NULL / empty college
        User::factory()->count(4)->create(['role' => 'student', 'college' => 'CAS']);
        User::factory()->count(2)->create(['role' => 'student', 'college' => 'CCS']);
        User::factory()->count(1)->create(['role' => 'student', 'college' => 'CTE']);
        User::factory()->count(2)->create(['role' => 'student', 'college' => null]);
        User::factory()->create(['role' => 'student', 'college' => '   ']);

        $tools = app(StudentTools::class);
        $result = $tools->getStudentStatistics($this->admin, ['group_by' => 'college']);

        $expectedTotal = User::where('role', 'student')->count();
        $this->assertEquals(11, $expectedTotal); // 1 from setUp + 10 created here

        $this->assertEquals($expectedTotal, $result['total_students']);
        $this->assertEquals('college', $result['group_by']);
        $this->assertEquals($expectedTotal, $result['grouped_records_total']);
        $this->assertTrue($result['reconciliation']['reconciled']);
        $this->assertEquals($expectedTotal, $result['reconciliation']['sum_of_groups']);

        // Group counts
        $labels = array_column($result['groups'], 'count', 'label');
        $this->assertEquals(4, $labels['CAS']);
        $this->assertEquals(2, $labels['CCS']);
        $this->assertEquals(1, $labels['CTE']);
        $this->assertEquals(4, $labels['Not specified']); // 3 created here + 1 setUp student
        $this->assertEquals(4, $result['unspecified_count']);
    }

    public function test_student_statistics_grouped_by_program(): void
    {
        User::factory()->count(3)->create(['role' => 'student', 'program' => 'BS Information Technology']);
        User::factory()->count(2)->create(['role' => 'student', 'program' => 'AB Psychology']);
        User::factory()->count(2)->create(['role' => 'student', 'program' => null]);

        $tools = app(StudentTools::class);
        $result = $tools->getStudentStatistics($this->admin, ['group_by' => 'program']);

        $expectedTotal = User::where('role', 'student')->count();
        $this->assertTrue($result['reconciliation']['reconciled']);
        $this->assertEquals($expectedTotal, $result['grouped_records_total']);

        $labels = array_column($result['groups'], 'count', 'label');
        $this->assertEquals(3, $labels['BS Information Technology']);
        $this->assertEquals(2, $labels['AB Psychology']);
        $this->assertEquals(3, $labels['Not specified']); // 2 created + 1 setUp student
    }

    public function test_student_statistics_grouped_by_verification_status(): void
    {
        User::factory()->count(3)->create(['role' => 'student', 'school_id_verification_status' => 'approved']);
        User::factory()->count(2)->create(['role' => 'student', 'school_id_verification_status' => 'rejected']);
        User::factory()->count(4)->create(['role' => 'student', 'school_id_verification_status' => 'pending']);

        $tools = app(StudentTools::class);
        $result = $tools->getStudentStatistics($this->admin, ['group_by' => 'verification_status']);

        $expectedTotal = User::where('role', 'student')->count();
        $this->assertTrue($result['reconciliation']['reconciled']);
        $this->assertEquals($expectedTotal, $result['grouped_records_total']);

        $labels = array_column($result['groups'], 'count', 'label');
        $this->assertEquals(3, $labels['approved']);
        $this->assertEquals(2, $labels['rejected']);
        // 4 created + 1 setUp student (which has pending verification)
        $this->assertEquals(5, $labels['pending']);
    }

    public function test_student_statistics_grouped_with_date_filter(): void
    {
        // 3 students this month (September 2026)
        User::factory()->create(['role' => 'student', 'college' => 'CAS', 'created_at' => Carbon::create(2026, 9, 2)]);
        User::factory()->create(['role' => 'student', 'college' => 'CAS', 'created_at' => Carbon::create(2026, 9, 5)]);
        User::factory()->create(['role' => 'student', 'college' => 'CBM', 'created_at' => Carbon::create(2026, 9, 8)]);

        // 2 students last month (August 2026)
        User::factory()->create(['role' => 'student', 'college' => 'CCS', 'created_at' => Carbon::create(2026, 8, 15)]);
        User::factory()->create(['role' => 'student', 'college' => 'CTE', 'created_at' => Carbon::create(2026, 8, 20)]);

        $tools = app(StudentTools::class);
        $result = $tools->getStudentStatistics($this->admin, [
            'period' => 'this_month',
            'group_by' => 'college',
        ]);

        $this->assertEquals(3, $result['total_students']);
        $this->assertEquals(3, $result['grouped_records_total']);
        $this->assertTrue($result['reconciliation']['reconciled']);

        $labels = array_column($result['groups'], 'count', 'label');
        $this->assertEquals(2, $labels['CAS']);
        $this->assertEquals(1, $labels['CBM']);
        $this->assertArrayNotHasKey('CCS', $labels);
        $this->assertArrayNotHasKey('CTE', $labels);
    }

    public function test_student_registration_trend_monthly_series_and_metrics(): void
    {
        // Add registrations across several months
        User::factory()->count(2)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 3, 10)]);
        User::factory()->count(4)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 4, 15)]);
        User::factory()->count(3)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 5, 20)]);
        User::factory()->count(1)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 8, 10)]); // last month
        User::factory()->count(2)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 9, 5)]);  // this month

        $tools = app(StudentTools::class);
        $trend = $tools->getStudentRegistrationTrend($this->admin, ['group_by' => 'month']);

        $this->assertArrayHasKey('series', $trend);
        $this->assertArrayHasKey('total_registrations', $trend);
        $this->assertArrayHasKey('current_month', $trend);
        $this->assertArrayHasKey('previous_month', $trend);
        $this->assertArrayHasKey('average_per_period', $trend);
        $this->assertArrayHasKey('trend_direction', $trend);
        $this->assertArrayHasKey('confidence', $trend);

        // 2 in current month (Sept 2026)
        $this->assertEquals(2, $trend['current_month']);
        // 1 in previous month (Aug 2026)
        $this->assertEquals(1, $trend['previous_month']);
        $this->assertEquals('increasing', $trend['trend_direction']);
    }

    public function test_student_registration_trend_weekly_and_daily(): void
    {
        User::factory()->count(2)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 9, 8)]);
        User::factory()->count(1)->create(['role' => 'student', 'created_at' => Carbon::create(2026, 9, 9)]);

        $tools = app(StudentTools::class);
        $weeklyTrend = $tools->getStudentRegistrationTrend($this->admin, ['group_by' => 'week']);
        $dailyTrend = $tools->getStudentRegistrationTrend($this->admin, ['group_by' => 'day']);

        $this->assertEquals('week', $weeklyTrend['group_by']);
        $this->assertNotEmpty($weeklyTrend['series']);

        $this->assertEquals('day', $dailyTrend['group_by']);
        $this->assertNotEmpty($dailyTrend['series']);
    }

    public function test_registration_trend_sparse_data_flags_low_confidence(): void
    {
        // Only 1 student exists (the one from setUp in Sept 2026)
        $tools = app(StudentTools::class);
        $trend = $tools->getStudentRegistrationTrend($this->admin, ['group_by' => 'month']);

        $this->assertEquals('low', $trend['confidence']);
        $this->assertStringContainsString('sparse', strtolower($trend['forecast_guidance']));
    }

    public function test_role_authorization_for_student_analytics_and_trends(): void
    {
        $registry = app(AiToolRegistry::class);

        $adminTools = collect($registry->getToolsForRole('admin'))->pluck('function.name')->all();
        $studentTools = collect($registry->getToolsForRole('student'))->pluck('function.name')->all();
        $landlordTools = collect($registry->getToolsForRole('landlord'))->pluck('function.name')->all();

        // Admin has access
        $this->assertContains('get_student_statistics', $adminTools);
        $this->assertContains('get_student_registration_trend', $adminTools);

        // Student and landlord cannot access admin aggregate tools
        $this->assertNotContains('get_student_statistics', $studentTools);
        $this->assertNotContains('get_student_registration_trend', $studentTools);
        $this->assertNotContains('get_student_statistics', $landlordTools);
        $this->assertNotContains('get_student_registration_trend', $landlordTools);

        // Executor throws authorization exception or returns error for unauthorized role
        $executor = app(AiToolExecutor::class);
        $resStudent = $executor->execute('get_student_statistics', ['group_by' => 'college'], $this->student);
        $this->assertArrayHasKey('error', $resStudent);

        $resLandlord = $executor->execute('get_student_registration_trend', [], $this->landlord);
        $this->assertArrayHasKey('error', $resLandlord);
    }

    public function test_prompt_builder_includes_analytics_and_forecasting_guardrails(): void
    {
        $builder = app(AiPromptBuilder::class);
        $prompt = $builder->buildSystemPrompt($this->admin);

        $this->assertStringContainsString('Analytics, Grouped Statistics & Database Reconciliation', $prompt);
        $this->assertStringContainsString('Forecasting and Registration Trend Guardrails', $prompt);
        $this->assertStringContainsString('NEVER attempt to manually count or group students by fetching a paginated list', $prompt);
        $this->assertStringContainsString('NO UNSOURCED EXTERNAL ASSUMPTIONS', $prompt);
        $this->assertStringContainsString('MINIMUM DATA RULE & LOW CONFIDENCE', $prompt);
    }

    public function test_end_to_end_chatbot_executes_grouped_statistics_tool(): void
    {
        User::factory()->count(4)->create(['role' => 'student', 'college' => 'CAS']);
        User::factory()->count(1)->create(['role' => 'student', 'college' => 'CBM']);
        User::factory()->count(1)->create(['role' => 'student', 'college' => 'CTE']);
        User::factory()->count(1)->create(['role' => 'student', 'college' => 'CCS']);
        User::factory()->count(9)->create(['role' => 'student', 'college' => null]);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => 'admin',
        ]);

        $assistantService = app(AiAssistantService::class);

        // Mock Hugging Face provider: first call returns tool_calls, second call provides final answer
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
                                        'id' => 'call_stats_123',
                                        'type' => 'function',
                                        'function' => [
                                            'name' => 'get_student_statistics',
                                            'arguments' => json_encode(['group_by' => 'college']),
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
                                'content' => "Narito ang breakdown ng 17 registered students by college:\n\n- CAS: 4\n- CBM: 1\n- CTE: 1\n- CCS: 1\n- Not specified: 10\n\nPinakamalaking group ang students na wala pang college information.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $response = $assistantService->handleMessage($conversation, $this->admin, 'stats ng students based on college');

        $this->assertArrayHasKey('reply', $response);
        $this->assertStringContainsString('CAS: 4', $response['reply']);
        $this->assertStringContainsString('Not specified: 10', $response['reply']);
        $this->assertArrayHasKey('actions', $response['meta']);
        $this->assertNotEmpty($response['meta']['actions']);
        $this->assertEquals('/admin/student-verifications', $response['meta']['actions'][0]['route']);
    }
}
