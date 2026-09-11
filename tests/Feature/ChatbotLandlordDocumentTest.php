<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\User;
use App\Services\AI\AiToolExecutor;
use App\Services\AI\AiToolRegistry;
use App\Services\LandlordDocumentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotLandlordDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected LandlordDocumentStatusService $statusService;
    protected AiToolRegistry $toolRegistry;
    protected AiToolExecutor $toolExecutor;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.huggingface.token' => 'test-hf-token',
            'services.huggingface.model' => 'deepseek-ai/DeepSeek-V4.1-Flash:novita',
            'services.huggingface.endpoint' => 'https://router.huggingface.co/v1/chat/completions',
            'chatbot.rate_limit_per_minute' => 20,
        ]);

        $this->statusService = app(LandlordDocumentStatusService::class);
        $this->toolRegistry = app(AiToolRegistry::class);
        $this->toolExecutor = app(AiToolExecutor::class);
    }

    protected function createCompleteStudent(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
            'full_name' => 'Juan Student',
            'email_verified_at' => now(),
            'profile_image_path' => 'profiles/test.jpg',
            'contact_number' => '09123456789',
            'birth_date' => '2004-01-01',
            'college' => 'College of Science',
            'program' => 'BS Computer Science',
            'year_level' => '1st Year',
            'gender' => 'Male',
            'address' => 'Sample Address',
            'emergency_contact_name' => 'Maria Parent',
            'emergency_contact_number' => '09123456780',
            'emergency_contact_relationship' => 'Mother',
            'parent_contact_name' => 'Pedro Parent',
            'parent_contact_number' => '09123456781',
            'parent_contact_address' => 'Sample Parent Address',
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => 'enrollments/test.pdf',
            'school_id_verification_status' => 'approved',
        ], $attributes));
    }

    /**
     * Create the exact 5-landlord dataset scenario described in the user prompt.
     */
    protected function seedExampleDataset(): array
    {
        // 1. Mark Nebres (ID 25 equivalent): BP uploaded & approved; SC missing
        $l1 = User::factory()->create(['role' => 'landlord', 'full_name' => 'Mark Nebres']);
        LandlordProfile::create([
            'user_id' => $l1->id,
            'business_permit_path' => 'landlords/' . $l1->id . '/permit.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => null,
            'safety_certificate_status' => 'not_submitted',
        ]);

        // 2. Mark Edniel Nebres: BP uploaded & approved; SC missing
        $l2 = User::factory()->create(['role' => 'landlord', 'full_name' => 'Mark Edniel Nebres']);
        LandlordProfile::create([
            'user_id' => $l2->id,
            'business_permit_path' => 'landlords/' . $l2->id . '/permit.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => null,
            'safety_certificate_status' => 'not_submitted',
        ]);

        // 3. Landlord01: BP missing; SC missing
        $l3 = User::factory()->create(['role' => 'landlord', 'full_name' => 'Landlord01']);
        LandlordProfile::create([
            'user_id' => $l3->id,
            'business_permit_path' => null,
            'business_permit_status' => 'not_submitted',
            'safety_certificate_path' => null,
            'safety_certificate_status' => 'not_submitted',
        ]);

        // 4. New Landlord: BP uploaded & approved; SC missing
        $l4 = User::factory()->create(['role' => 'landlord', 'full_name' => 'New Landlord']);
        LandlordProfile::create([
            'user_id' => $l4->id,
            'business_permit_path' => 'landlords/' . $l4->id . '/permit.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => null,
            'safety_certificate_status' => 'not_submitted',
        ]);

        // 5. Luna Wing: BP uploaded & approved; SC missing
        $l5 = User::factory()->create(['role' => 'landlord', 'full_name' => 'Luna Wing']);
        LandlordProfile::create([
            'user_id' => $l5->id,
            'business_permit_path' => 'landlords/' . $l5->id . '/permit.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => null,
            'safety_certificate_status' => 'not_submitted',
        ]);

        return [$l1, $l2, $l3, $l4, $l5];
    }

    public function test_get_landlord_document_statistics_returns_exact_counts_on_example_dataset(): void
    {
        $this->seedExampleDataset();

        $stats = $this->statusService->getAggregateStatistics();

        $this->assertEquals(5, $stats['total_landlords']);

        // Business Permit counts
        $this->assertEquals(1, $stats['business_permit']['missing']);
        $this->assertEquals(0, $stats['business_permit']['pending']);
        $this->assertEquals(4, $stats['business_permit']['approved']);
        $this->assertEquals(0, $stats['business_permit']['rejected']);

        // Safety Certificate counts
        $this->assertEquals(5, $stats['safety_certificate']['missing']);
        $this->assertEquals(0, $stats['safety_certificate']['pending']);
        $this->assertEquals(0, $stats['safety_certificate']['approved']);
        $this->assertEquals(0, $stats['safety_certificate']['rejected']);

        // Landlord-level affected counts vs document instances
        $this->assertEquals(5, $stats['landlords_with_missing_documents']);
        $this->assertEquals(0, $stats['landlords_with_pending_documents']);
        $this->assertEquals(0, $stats['landlords_with_rejected_documents']);
        $this->assertEquals(6, $stats['total_missing_document_instances']);
        $this->assertEquals(0, $stats['fully_document_complete_landlords']);
    }

    public function test_get_landlords_with_missing_documents_identifies_affected_landlords(): void
    {
        $this->seedExampleDataset();

        $allMissing = $this->statusService->getLandlordsWithMissingDocuments();
        $this->assertEquals(5, $allMissing['total_affected_landlords']);

        // Landlord01 is missing both documents
        $landlord01 = collect($allMissing['landlords'])->firstWhere('name', 'Landlord01');
        $this->assertNotNull($landlord01);
        $this->assertContains('Business Permit', $landlord01['missing_documents']);
        $this->assertContains('Safety Certificate', $landlord01['missing_documents']);

        // Filter only missing Business Permits
        $bpMissing = $this->statusService->getLandlordsWithMissingDocuments('business_permit');
        $this->assertEquals(1, $bpMissing['total_affected_landlords']);
        $this->assertEquals('Landlord01', $bpMissing['landlords'][0]['name']);

        // Filter only missing Safety Certificates
        $scMissing = $this->statusService->getLandlordsWithMissingDocuments('safety_certificate');
        $this->assertEquals(5, $scMissing['total_affected_landlords']);
    }

    public function test_missing_vs_pending_distinction(): void
    {
        $landlord = User::factory()->create(['role' => 'landlord', 'full_name' => 'Pending Landlord']);
        LandlordProfile::create([
            'user_id' => $landlord->id,
            'business_permit_path' => 'landlords/' . $landlord->id . '/permit.pdf',
            'business_permit_status' => 'pending',
            'safety_certificate_path' => 'landlords/' . $landlord->id . '/safety.pdf',
            'safety_certificate_status' => 'pending',
        ]);

        $summary = $this->statusService->getLandlordDocumentSummary($landlord);

        $this->assertEquals('pending', $summary['business_permit_status']);
        $this->assertEquals('pending', $summary['safety_certificate_status']);
        $this->assertFalse($summary['has_missing_documents']);
        $this->assertTrue($summary['has_pending_documents']);
        $this->assertEmpty($summary['missing_documents']);
        $this->assertContains('Business Permit', $summary['pending_documents']);
        $this->assertContains('Safety Certificate', $summary['pending_documents']);
    }

    public function test_mixed_statuses_and_full_completeness(): void
    {
        $compliantLandlord = User::factory()->create(['role' => 'landlord', 'full_name' => 'Compliant Landlord']);
        LandlordProfile::create([
            'user_id' => $compliantLandlord->id,
            'business_permit_path' => 'permits/1.pdf',
            'business_permit_status' => 'approved',
            'safety_certificate_path' => 'safety/1.pdf',
            'safety_certificate_status' => 'approved',
        ]);

        $summary = $this->statusService->getLandlordDocumentSummary($compliantLandlord);
        $this->assertTrue($summary['is_fully_compliant']);
        $this->assertFalse($summary['has_missing_documents']);
        $this->assertFalse($summary['has_pending_documents']);

        $stats = $this->statusService->getAggregateStatistics();
        $this->assertEquals(1, $stats['fully_document_complete_landlords']);
    }

    public function test_admin_can_execute_document_intelligence_tools(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->seedExampleDataset();

        // 1. get_landlord_document_statistics tool execution
        $statsResult = $this->toolExecutor->execute('get_landlord_document_statistics', [], $admin);
        $this->assertEquals(5, $statsResult['total_landlords']);
        $this->assertEquals(6, $statsResult['total_missing_document_instances']);
        $this->assertEquals('/admin/approvals/landlords?tab=permits', $statsResult['action_url']);

        // 2. get_landlords_with_missing_documents tool execution
        $missingResult = $this->toolExecutor->execute('get_landlords_with_missing_documents', [], $admin);
        $this->assertEquals(5, $missingResult['total_affected_landlords']);
        $this->assertEquals('/admin/approvals/landlords?tab=permits', $missingResult['action_url']);

        // 3. get_pending_landlord_documents tool execution
        $pendingResult = $this->toolExecutor->execute('get_pending_landlord_documents', [], $admin);
        $this->assertEquals(0, $pendingResult['total_pending_landlords']);
    }

    public function test_landlord_can_only_query_own_document_status(): void
    {
        [$l1, $l2, $l3] = $this->seedExampleDataset();

        // Landlord 1 querying own status
        $statusResult = $this->toolExecutor->execute('get_landlord_document_status', [], $l1);
        $this->assertEquals($l1->id, $statusResult['landlord_id']);
        $this->assertEquals('approved', $statusResult['business_permit_status']);
        $this->assertEquals('missing', $statusResult['safety_certificate_status']);

        // Landlord 1 attempting to query Landlord 3's ID - should ignore landlord_id arg and stay scoped to self
        $attemptOther = $this->toolExecutor->execute('get_landlord_document_status', ['landlord_id' => $l3->id], $l1);
        $this->assertEquals($l1->id, $attemptOther['landlord_id']);
    }

    public function test_student_is_unauthorized_for_landlord_document_tools(): void
    {
        $student = $this->createCompleteStudent();

        $this->assertFalse($this->toolRegistry->isAuthorized('get_landlord_document_statistics', 'student'));
        $this->assertFalse($this->toolRegistry->isAuthorized('get_landlords_with_missing_documents', 'student'));
        $this->assertFalse($this->toolRegistry->isAuthorized('get_pending_landlord_documents', 'student'));
        $this->assertFalse($this->toolRegistry->isAuthorized('get_landlord_document_status', 'student'));

        $execResult = $this->toolExecutor->execute('get_landlord_document_statistics', [], $student);
        $this->assertArrayHasKey('error', $execResult);
        $this->assertStringContainsString('Unauthorized', $execResult['error']);
    }

    public function test_admin_ui_and_service_counts_match(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->seedExampleDataset();

        $response = $this->actingAs($admin)->get('/admin/approvals/landlords?tab=permits');
        $response->assertStatus(200);

        // Verify counts passed to the Blade view
        $viewCounts = $response->viewData('permitCounts');
        $stats = $this->statusService->getAggregateStatistics();

        $this->assertEquals($stats['total_landlords'], $viewCounts['all']);
        $this->assertEquals($stats['landlords_with_missing_documents'], $viewCounts['missing']);
        $this->assertEquals($stats['landlords_with_pending_documents'], $viewCounts['pending']);
        $this->assertEquals(4, $viewCounts['approved']);
    }

    public function test_chatbot_answers_landlord_missing_documents_with_action_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->seedExampleDataset();

        $callCount = 0;
        Http::fake([
            'https://router.huggingface.co/v1/chat/completions' => function ($request) use (&$callCount) {
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
                                            'id' => 'call_doc_stats',
                                            'type' => 'function',
                                            'function' => [
                                                'name' => 'get_landlord_document_statistics',
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
                                'content' => "Mayroong **6 missing document records** ngayon sa OBHS:\n\n• **Business Permit:** 1 missing\n• **Safety Certificate:** 5 missing\n\nLahat ng 5 registered landlords ay may kulang na dokumento. Maaari mong i-review sa Review Landlord Documents.",
                            ],
                        ],
                    ],
                ], 200);
            },
        ]);

        $response = $this->actingAs($admin)->postJson('/chatbot/message', [
            'content' => 'Ilan ang missing documents ng landlord?',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reply',
            'actions' => [
                '*' => ['type', 'label', 'route', 'url'],
            ],
        ]);

        $actions = $response->json('actions');
        $this->assertNotEmpty($actions);
        $this->assertEquals('Review Landlord Documents', $actions[0]['label']);
        $this->assertEquals('/admin/approvals/landlords?tab=permits', $actions[0]['route']);
    }
}
