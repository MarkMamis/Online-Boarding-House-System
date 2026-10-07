<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminIncidentReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_incident_reports_by_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        Report::create([
            'user_id' => $student->id,
            'title' => 'Pending Complaint',
            'description' => 'Noise issue',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        Report::create([
            'user_id' => $student->id,
            'title' => 'Resolved Complaint',
            'description' => 'Water issue fixed',
            'priority' => 'high',
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $responsePending = $this->actingAs($admin)->get('/admin/reports?status=pending');
        $responsePending->assertOk();
        $responsePending->assertSee('Pending Complaint');
        $responsePending->assertDontSee('Resolved Complaint');

        $responseResolved = $this->actingAs($admin)->get('/admin/reports?status=resolved');
        $responseResolved->assertOk();
        $responseResolved->assertSee('Resolved Complaint');
        $responseResolved->assertDontSee('Pending Complaint');
    }

    public function test_non_admin_cannot_access_incident_reports_workspace(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/admin/reports');
        $response->assertForbidden();
    }
}
