<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInstitutionalReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_analytics_occupancy_csv(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'MinSU Heights Boarding House',
            'address' => 'Masipit, Calapan City',
            'approval_status' => 'approved',
        ]);

        Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'price' => 1500,
            'capacity' => 4,
            'slots_available' => 2,
            'status' => 'available',
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics/export?report_preset=occupancy_availability');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('MinSU Heights Boarding House', $response->streamedContent());
        $this->assertStringContainsString('Room Occupancy Rate (%)', $response->streamedContent());
    }

    public function test_non_admin_cannot_export_analytics_csv(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/admin/analytics/export');
        $response->assertForbidden();
    }

    public function test_admin_can_preview_all_six_report_presets_in_dedicated_workspace(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $presets = [
            'active_boarders' => 'Active Boarded Students',
            'property_distribution' => 'Boarding House Distribution',
            'academic_distribution' => 'Academic Distribution',
            'occupancy_availability' => 'Occupancy and Availability',
            'compliance_summary' => 'Landlord Compliance Summary',
            'incident_summary' => 'Incident Reports Summary',
        ];

        foreach ($presets as $preset => $title) {
            $response = $this->actingAs($admin)->get('/admin/institutional-reports?report_preset=' . $preset);
            $response->assertOk();
            $response->assertViewIs('admin.institutional_reports.index');
            $response->assertViewHas('reportData');
            $response->assertSee($title);
        }
    }

    public function test_admin_can_render_dedicated_print_view_for_all_presets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $presets = [
            'active_boarders',
            'property_distribution',
            'academic_distribution',
            'occupancy_availability',
            'compliance_summary',
            'incident_summary',
        ];

        foreach ($presets as $preset) {
            $response = $this->actingAs($admin)->get('/admin/institutional-reports/print?report_preset=' . $preset);
            $response->assertOk();
            $response->assertViewIs('admin.institutional_reports.print');
            $response->assertSee('Mindoro State University');
            $response->assertSee('Online Boarding House System');
            $response->assertSee('Certified Correct');
            $response->assertDontSee('admin-sidebar');
            $response->assertDontSee('admin-topbar');
        }
    }

    public function test_admin_can_export_csv_for_all_presets_with_filters(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $presets = [
            'active_boarders',
            'property_distribution',
            'academic_distribution',
            'occupancy_availability',
            'compliance_summary',
            'incident_summary',
        ];

        foreach ($presets as $preset) {
            $response = $this->actingAs($admin)->get('/admin/institutional-reports/export?report_preset=' . $preset . '&period_preset=this_month');
            $response->assertOk();
            $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
            $content = $response->streamedContent();
            $this->assertNotEmpty($content);
        }
    }

    public function test_non_admin_cannot_access_institutional_reports_or_print(): void
    {
        $student = User::factory()->create(['role' => 'student', 'email_verified_at' => now()]);
        $landlord = User::factory()->create(['role' => 'landlord', 'email_verified_at' => now()]);

        // Student
        $this->actingAs($student)->get('/admin/institutional-reports')->assertForbidden();
        $this->actingAs($student)->get('/admin/institutional-reports/print')->assertForbidden();
        $this->actingAs($student)->get('/admin/institutional-reports/export')->assertForbidden();

        // Landlord
        $this->actingAs($landlord)->get('/admin/institutional-reports')->assertForbidden();
        $this->actingAs($landlord)->get('/admin/institutional-reports/print')->assertForbidden();
        $this->actingAs($landlord)->get('/admin/institutional-reports/export')->assertForbidden();

        // Guest
        $this->get('/admin/institutional-reports')->assertForbidden();
        $this->get('/admin/institutional-reports/print')->assertForbidden();
        $this->get('/admin/institutional-reports/export')->assertForbidden();
    }
}
