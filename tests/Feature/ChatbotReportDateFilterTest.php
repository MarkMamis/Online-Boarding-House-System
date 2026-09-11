<?php

namespace Tests\Feature;

use App\Models\ChatbotConversation;
use App\Models\Report;
use App\Models\User;
use App\Services\AI\AiAssistantService;
use App\Services\AI\AiPromptBuilder;
use App\Services\AI\AiToolExecutor;
use App\Services\AI\AiToolRegistry;
use App\Services\AI\Support\AiDateRangeResolver;
use App\Services\AI\Tools\ReportTools;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotReportDateFilterTest extends TestCase
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
        // Anchor now to a deterministic local time: Friday, September 11, 2026, 10:00:00 AM
        Carbon::setTestNow(Carbon::create(2026, 9, 11, 10, 0, 0, $this->tz));

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'contact_number' => '09171234567',
        ]);

        $this->student = User::factory()->create([
            'role' => 'student',
            'name' => 'Juan Student',
            'email' => 'student@example.com',
            'contact_number' => '09181234567',
        ]);

        $this->landlord = User::factory()->create([
            'role' => 'landlord',
            'name' => 'Pedro Landlord',
            'email' => 'landlord@example.com',
            'contact_number' => '09191234567',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * Helper to create a report with specific timestamps and fields.
     */
    protected function createReport(array $attributes = []): Report
    {
        $submittedAt = $attributes['created_at'] ?? now();
        unset($attributes['created_at']);

        $resolvedAt = $attributes['resolved_at'] ?? null;
        unset($attributes['resolved_at']);

        $report = new Report(array_merge([
            'user_id' => $this->student->id,
            'title' => 'Test Issue',
            'description' => 'Test issue description',
            'status' => 'pending',
            'priority' => 'medium',
        ], $attributes));

        $report->created_at = $submittedAt;
        $report->updated_at = $submittedAt;
        if ($resolvedAt) {
            $report->resolved_at = $resolvedAt;
        }
        $report->save();

        return $report;
    }

    public function test_ai_date_range_resolver_boundaries(): void
    {
        // Today
        $today = AiDateRangeResolver::resolve(['period' => 'today'], $this->tz);
        $this->assertTrue($today['is_filtered']);
        $this->assertEquals('today', $today['label']);
        $this->assertEquals('2026-09-11 00:00:00', $today['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-11 23:59:59', $today['end']->format('Y-m-d H:i:s'));

        // Yesterday
        $yesterday = AiDateRangeResolver::resolve(['period' => 'yesterday'], $this->tz);
        $this->assertEquals('yesterday', $yesterday['label']);
        $this->assertEquals('2026-09-10 00:00:00', $yesterday['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-10 23:59:59', $yesterday['end']->format('Y-m-d H:i:s'));

        // This Week (Monday Sep 7 to Sep 11)
        $thisWeek = AiDateRangeResolver::resolve(['period' => 'this_week'], $this->tz);
        $this->assertEquals('this_week', $thisWeek['label']);
        $this->assertEquals('2026-09-07 00:00:00', $thisWeek['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-11 23:59:59', $thisWeek['end']->format('Y-m-d H:i:s'));

        // This Month (Sep 1 to Sep 11)
        $thisMonth = AiDateRangeResolver::resolve(['period' => 'this_month'], $this->tz);
        $this->assertEquals('this_month', $thisMonth['label']);
        $this->assertEquals('2026-09-01 00:00:00', $thisMonth['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-11 23:59:59', $thisMonth['end']->format('Y-m-d H:i:s'));

        // Custom Date Range
        $custom = AiDateRangeResolver::resolve([
            'date_from' => '2026-09-01',
            'date_to' => '2026-09-05',
        ], $this->tz);
        $this->assertEquals('2026-09-01 00:00:00', $custom['start']->format('Y-m-d H:i:s'));
        $this->assertEquals('2026-09-05 23:59:59', $custom['end']->format('Y-m-d H:i:s'));
    }

    public function test_get_report_statistics_for_today_returns_exact_counts(): void
    {
        // 2 reports submitted today
        $this->createReport(['title' => 'Today 1', 'status' => 'pending', 'priority' => 'high', 'created_at' => now()]);
        $this->createReport(['title' => 'Today 2', 'status' => 'in_progress', 'priority' => 'medium', 'created_at' => now()->subHours(2)]);

        // 1 report submitted yesterday
        $this->createReport(['title' => 'Yesterday 1', 'status' => 'pending', 'priority' => 'low', 'created_at' => now()->subDay()]);

        // 1 report submitted last month
        $this->createReport(['title' => 'Last Month', 'status' => 'resolved', 'priority' => 'high', 'created_at' => now()->subMonth()]);

        $tools = app(ReportTools::class);
        $result = $tools->getReportStatistics($this->admin, ['period' => 'today']);

        $this->assertTrue($result['period']['is_filtered']);
        $this->assertEquals('today', $result['period']['label']);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals(2, $result['total_reports_in_period']);
        $this->assertEquals(1, $result['statuses']['pending']);
        $this->assertEquals(1, $result['statuses']['in_progress']);
        $this->assertEquals(0, $result['statuses']['resolved']);
        $this->assertEquals(1, $result['priorities']['high']);
        $this->assertEquals(1, $result['priorities']['medium']);
        $this->assertEquals(0, $result['priorities']['low']);
        $this->assertEquals(4, $result['all_time_total']);
        $this->assertEquals('/admin/reports', $result['action_url']);
    }

    public function test_get_report_statistics_high_priority_today(): void
    {
        $this->createReport(['title' => 'High 1', 'status' => 'pending', 'priority' => 'high', 'created_at' => now()]);
        $this->createReport(['title' => 'Medium 1', 'status' => 'pending', 'priority' => 'medium', 'created_at' => now()]);

        $tools = app(ReportTools::class);
        $result = $tools->getReportStatistics($this->admin, [
            'period' => 'today',
            'priority' => 'high',
        ]);

        $this->assertEquals(1, $result['total']);
        $this->assertEquals(2, $result['total_reports_in_period']);
        $this->assertEquals(1, $result['priorities']['high']);
    }

    public function test_get_report_statistics_yesterday_and_this_week(): void
    {
        // 3 submitted yesterday (Sep 10)
        $this->createReport(['title' => 'Yest 1', 'created_at' => now()->subDay()]);
        $this->createReport(['title' => 'Yest 2', 'created_at' => now()->subDay()]);
        $this->createReport(['title' => 'Yest 3', 'created_at' => now()->subDay()]);

        // 1 submitted today (Sep 11)
        $this->createReport(['title' => 'Today 1', 'created_at' => now()]);

        $tools = app(ReportTools::class);

        // Yesterday check
        $yestResult = $tools->getReportStatistics($this->admin, ['period' => 'yesterday']);
        $this->assertEquals(3, $yestResult['total']);

        // This week check (total 4)
        $weekResult = $tools->getReportStatistics($this->admin, ['period' => 'this_week']);
        $this->assertEquals(4, $weekResult['total']);
    }

    public function test_zero_reports_today_returns_zero_count_naturally(): void
    {
        // Only past reports exist
        $this->createReport(['title' => 'Past 1', 'created_at' => now()->subDays(5)]);
        $this->createReport(['title' => 'Past 2', 'created_at' => now()->subDays(10)]);

        $tools = app(ReportTools::class);
        $result = $tools->getReportStatistics($this->admin, ['period' => 'today']);

        $this->assertEquals(0, $result['total']);
        $this->assertEquals(0, $result['total_reports_in_period']);
        $this->assertEquals(2, $result['all_time_total']);
    }

    public function test_resolved_today_uses_resolved_at_timestamp(): void
    {
        // Created 10 days ago, but resolved today!
        $this->createReport([
            'title' => 'Old Issue Resolved Today',
            'status' => 'resolved',
            'created_at' => now()->subDays(10),
            'resolved_at' => now(),
        ]);

        // Created today, but pending
        $this->createReport([
            'title' => 'Created Today Pending',
            'status' => 'pending',
            'created_at' => now(),
        ]);

        $tools = app(ReportTools::class);

        // Query resolved today with date_basis: resolved_at
        $resolvedResult = $tools->getReportStatistics($this->admin, [
            'period' => 'today',
            'status' => 'resolved',
            'date_basis' => 'resolved_at',
        ]);

        $this->assertEquals(1, $resolvedResult['total']);
        $this->assertEquals('resolved_at', $resolvedResult['period']['date_basis']);
    }

    public function test_midnight_and_timezone_boundary(): void
    {
        // Exactly at 2026-09-11 00:00:01 (today start)
        $startOfToday = Carbon::create(2026, 9, 11, 0, 0, 1, $this->tz);
        $this->createReport(['title' => 'Midnight Edge', 'created_at' => $startOfToday]);

        // At 2026-09-10 23:59:59 (yesterday end)
        $endOfYesterday = Carbon::create(2026, 9, 10, 23, 59, 59, $this->tz);
        $this->createReport(['title' => 'Yesterday Edge', 'created_at' => $endOfYesterday]);

        $tools = app(ReportTools::class);

        $todayResult = $tools->getReportStatistics($this->admin, ['period' => 'today']);
        $this->assertEquals(1, $todayResult['total']);

        $yesterdayResult = $tools->getReportStatistics($this->admin, ['period' => 'yesterday']);
        $this->assertEquals(1, $yesterdayResult['total']);
    }

    public function test_search_reports_tool_supports_date_filtering(): void
    {
        $this->createReport(['title' => 'Water Leaking', 'priority' => 'high', 'created_at' => now()]);
        $this->createReport(['title' => 'Noise Complaint', 'priority' => 'low', 'created_at' => now()->subDay()]);

        $tools = app(ReportTools::class);
        $result = $tools->searchReports($this->admin, [
            'period' => 'today',
            'priority' => 'high',
        ]);

        $this->assertEquals(1, $result['total_matching']);
        $this->assertEquals('Water Leaking', $result['reports'][0]['title']);
        $this->assertEquals('high', $result['reports'][0]['priority']);
        $this->assertEquals('/admin/reports', $result['action_url']);
    }

    public function test_role_authorization_for_report_statistics(): void
    {
        $registry = app(AiToolRegistry::class);

        // Admin is authorized
        $this->assertTrue($registry->isAuthorized('get_report_statistics', 'admin'));
        $this->assertTrue($registry->isAuthorized('count_reports', 'admin'));
        $this->assertTrue($registry->isAuthorized('search_reports', 'admin'));

        // Landlord is NOT authorized for system-wide report tools
        $this->assertFalse($registry->isAuthorized('get_report_statistics', 'landlord'));
        $this->assertFalse($registry->isAuthorized('count_reports', 'landlord'));
        $this->assertFalse($registry->isAuthorized('search_reports', 'landlord'));

        // Student is NOT authorized for system-wide report tools
        $this->assertFalse($registry->isAuthorized('get_report_statistics', 'student'));
        $this->assertFalse($registry->isAuthorized('count_reports', 'student'));
        $this->assertFalse($registry->isAuthorized('search_reports', 'student'));

        // Student is authorized for get_my_reports
        $this->assertTrue($registry->isAuthorized('get_my_reports', 'student'));
    }

    public function test_ai_prompt_builder_includes_local_datetime_and_rules(): void
    {
        $promptBuilder = app(AiPromptBuilder::class);
        $prompt = $promptBuilder->buildSystemPrompt($this->admin);

        $this->assertStringContainsString('CURRENT SYSTEM DATE & TIME:', $prompt);
        $this->assertStringContainsString('2026-09-11', $prompt);
        $this->assertStringContainsString('Time-Awareness & Date Filtering:', $prompt);
        $this->assertStringContainsString('DIRECT ANSWER FIRST:', $prompt);
    }

    public function test_chatbot_ai_assistant_date_filtered_report_flow(): void
    {
        config(['services.huggingface.token' => 'test-hf-token']);

        $this->createReport(['title' => 'Broken Faucet', 'status' => 'pending', 'priority' => 'high', 'created_at' => now()]);
        $this->createReport(['title' => 'Noisy Tenant', 'status' => 'in_progress', 'priority' => 'medium', 'created_at' => now()]);

        $conversation = ChatbotConversation::create([
            'user_id' => $this->admin->id,
            'role' => $this->admin->role,
            'title' => 'Admin Test Session',
        ]);

        // Mock Hugging Face: first step calls get_report_statistics with period='today', second step returns final text
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
                                        'id' => 'call_report_stats_123',
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
                                'content' => "May **2 reports na na-submit today**.\n\n- **Pending:** 1\n- **In Progress:** 1\n- **Resolved:** 0\n\nMay 1 High Priority report na nangangailangan ng agarang atensyon sa /admin/reports.",
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $assistant = app(AiAssistantService::class);
        $response = $assistant->handleMessage($conversation, $this->admin, 'ilan ang reports today?');

        $this->assertStringContainsString('2 reports na na-submit today', $response['reply']);
        $this->assertNotEmpty($response['meta']['actions']);
        $this->assertEquals('/admin/reports', $response['meta']['actions'][0]['route']);
        $this->assertEquals('View Reports', $response['meta']['actions'][0]['label']);
    }
}
