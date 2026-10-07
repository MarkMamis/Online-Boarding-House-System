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

        $response = $this->actingAs($admin)->get('/admin/analytics/export');

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
}
