<?php

namespace Tests\Feature;

use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActionCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_action_center_summary_and_deep_links(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $landlord = User::factory()->create([
            'role' => 'landlord',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Pending Villa',
            'address' => 'Calapan City',
            'approval_status' => 'pending',
        ]);

        Report::create([
            'user_id' => $student->id,
            'title' => 'Urgent Electrical Concern',
            'description' => 'Sparks observed',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/admin/action-center');

        $response->assertOk();
        $response->assertSee('Admin Action Center');
        $response->assertSee('Pending Villa');
        $response->assertSee('Urgent Electrical Concern');
    }

    public function test_non_admin_cannot_access_action_center(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/admin/action-center');
        $response->assertForbidden();
    }
}
