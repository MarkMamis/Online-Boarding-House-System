<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminSecurityFoundationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivated_account_is_logged_out_by_middleware(): void
    {
        $landlord = User::factory()->create([
            'role' => 'landlord',
            'is_active' => false,
            'email_verified_at' => now(),
            'onboarding_complete' => true,
        ]);

        $response = $this->actingAs($landlord)->get('/landlord/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_active_account_can_access_authorized_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $userA = User::factory()->create([
            'role' => 'landlord',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $userB = User::factory()->create([
            'role' => 'landlord',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $notification = DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\SystemNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $userA->id,
            'data' => [
                'title' => 'Personal alert for user A',
                'message' => 'Confidential update',
            ],
            'read_at' => null,
        ]);

        // User B attempts to mark User A's notification as read
        $response = $this->actingAs($userB)->post('/notifications/' . $notification->id . '/read');

        $response->assertNotFound();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $userA = User::factory()->create([
            'role' => 'landlord',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $notification = DatabaseNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\SystemNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $userA->id,
            'data' => [
                'title' => 'Personal alert for user A',
                'message' => 'Confidential update',
            ],
            'read_at' => null,
        ]);

        $response = $this->actingAs($userA)->post('/notifications/' . $notification->id . '/read');

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_admin_update_student_profile_does_not_permit_role_escalation(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put('/admin/users/students/' . $student->id, [
            'full_name' => 'Updated Student Name',
            'email' => $student->email,
            'role' => 'admin', // Malicious attempt to escalate role
        ]);

        $response->assertRedirect();
        $this->assertEquals('student', $student->fresh()->role);
        $this->assertEquals('Updated Student Name', $student->fresh()->full_name);
    }

    public function test_report_status_update_validates_status_and_records_resolved_at(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = User::factory()->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $report = Report::create([
            'user_id' => $student->id,
            'title' => 'Broken Water Pipe',
            'description' => 'Water is leaking in room 102',
            'priority' => 'high',
            'status' => 'pending',
        ]);

        // Invalid status attempt
        $invalidResponse = $this->actingAs($admin)->put('/admin/reports/' . $report->id, [
            'status' => 'invalid_status',
        ]);
        $invalidResponse->assertSessionHasErrors('status');

        // Valid resolve
        $validResponse = $this->actingAs($admin)->put('/admin/reports/' . $report->id, [
            'status' => 'resolved',
            'admin_response' => 'Maintenance team dispatched and pipe replaced.',
        ]);

        $validResponse->assertRedirect('/admin/reports');
        $freshReport = $report->fresh();
        $this->assertEquals('resolved', $freshReport->status);
        $this->assertNotNull($freshReport->resolved_at);
        $this->assertFalse($freshReport->response_read);
    }
}
