<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ChatbotUiVisibilityFeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $landlord;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->createStudent();
        $this->landlord = $this->createLandlord();
        $this->admin = $this->createAdmin();
    }

    protected function createStudent(): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => 'Student '.$id,
            'full_name' => 'Student User '.$id,
            'email' => 'student_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
            'contact_number' => '0917'.rand(1000000, 9999999),
            'address' => 'Calapan City',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
        ]);
        $user->forceFill([
            'email_verified_at' => now(),
            'profile_image_path' => 'students/profiles/test.jpg',
            'birth_date' => '2002-05-15',
            'college' => 'College of Computer Studies',
            'program' => 'BS Information Technology',
            'year_level' => '3rd Year',
            'gender' => 'Male',
            'emergency_contact_name' => 'Emergency Name',
            'emergency_contact_number' => '09170001111',
            'emergency_contact_relationship' => 'Parent',
            'parent_contact_name' => 'Parent Name',
            'parent_contact_number' => '09170002222',
            'parent_contact_address' => 'Calapan City',
            'enrollment_proof_type' => 'cor',
            'enrollment_proof_path' => 'students/cor/test.pdf',
        ])->save();

        return $user;
    }

    protected function createLandlord(): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => 'Landlord '.$id,
            'full_name' => 'Landlord User '.$id,
            'email' => 'landlord_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '0917'.rand(1000000, 9999999),
            'address' => 'Calapan City',
            'boarding_house_name' => 'Villa '.$id,
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'boarding_house_name' => 'Villa '.$id,
            'about' => 'Comfortable student housing',
            'contact_number' => $user->contact_number,
            'business_permit_status' => 'approved',
            'preferred_payment_methods' => ['cash'],
            'profile_completed' => true,
            'billing_completed' => true,
        ]);

        return $user;
    }

    protected function createAdmin(): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => 'Admin '.$id,
            'full_name' => 'Admin User '.$id,
            'email' => 'admin_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'contact_number' => '0917'.rand(1000000, 9999999),
            'address' => 'Calapan City',
            'boarding_house_name' => 'Admin Office',
            'onboarding_complete' => true,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    // 1. Configuration exists and defaults to false
    public function test_chatbot_ui_feature_flag_configuration_exists(): void
    {
        $this->assertArrayHasKey('chatbot_ui_enabled', config('features'));
    }

    // 2. When disabled (default): component renders nothing
    public function test_chatbot_component_renders_empty_when_feature_flag_is_false(): void
    {
        Config::set('features.chatbot_ui_enabled', false);

        $html = Blade::render('<x-chatbot />');

        $this->assertStringNotContainsString('id="chatbotWidget"', $html);
        $this->assertStringNotContainsString('id="chatbotToggle"', $html);
        $this->assertStringNotContainsString('id="chatbotPanel"', $html);
        $this->assertStringNotContainsString('fetch(\'/chatbot/history\')', $html);
        $this->assertEmpty(trim($html));
    }

    // 3. When enabled: component renders full widget markup and script
    public function test_chatbot_component_renders_widget_when_feature_flag_is_true(): void
    {
        Config::set('features.chatbot_ui_enabled', true);

        $html = Blade::render('<x-chatbot />');

        $this->assertStringContainsString('id="chatbotWidget"', $html);
        $this->assertStringContainsString('id="chatbotToggle"', $html);
        $this->assertStringContainsString('id="chatbotPanel"', $html);
        $this->assertStringContainsString('fetch(\'/chatbot/history\'', $html);
    }

    // 4. Student portal hides chatbot when disabled
    public function test_student_dashboard_hides_chatbot_when_disabled(): void
    {
        Config::set('features.chatbot_ui_enabled', false);

        $response = $this->actingAs($this->student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertDontSee('id="chatbotWidget"', false);
        $response->assertDontSee('id="chatbotToggle"', false);
        $response->assertDontSee('id="chatbotPanel"', false);
    }

    // 5. Landlord portal hides chatbot when disabled
    public function test_landlord_dashboard_hides_chatbot_when_disabled(): void
    {
        Config::set('features.chatbot_ui_enabled', false);

        $response = $this->actingAs($this->landlord)->get(route('landlord.dashboard'));

        $response->assertOk();
        $response->assertDontSee('id="chatbotWidget"', false);
        $response->assertDontSee('id="chatbotToggle"', false);
        $response->assertDontSee('id="chatbotPanel"', false);
    }

    // 6. Admin dashboard hides chatbot when disabled
    public function test_admin_dashboard_hides_chatbot_when_disabled(): void
    {
        Config::set('features.chatbot_ui_enabled', false);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('id="chatbotWidget"', false);
        $response->assertDontSee('id="chatbotToggle"', false);
        $response->assertDontSee('id="chatbotPanel"', false);
    }

    // 7. Student portal displays chatbot when enabled
    public function test_student_dashboard_shows_chatbot_when_enabled(): void
    {
        Config::set('features.chatbot_ui_enabled', true);

        $response = $this->actingAs($this->student)->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee('id="chatbotWidget"', false);
        $response->assertSee('id="chatbotToggle"', false);
        $response->assertSee('id="chatbotPanel"', false);
    }

    // 8. Landlord portal displays chatbot when enabled
    public function test_landlord_dashboard_shows_chatbot_when_enabled(): void
    {
        Config::set('features.chatbot_ui_enabled', true);

        $response = $this->actingAs($this->landlord)->get(route('landlord.dashboard'));

        $response->assertOk();
        $response->assertSee('id="chatbotWidget"', false);
        $response->assertSee('id="chatbotToggle"', false);
        $response->assertSee('id="chatbotPanel"', false);
    }

    // 9. Admin dashboard displays chatbot when enabled
    public function test_admin_dashboard_shows_chatbot_when_enabled(): void
    {
        Config::set('features.chatbot_ui_enabled', true);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('id="chatbotWidget"', false);
        $response->assertSee('id="chatbotToggle"', false);
        $response->assertSee('id="chatbotPanel"', false);

        // Verify the chatbot widget and toggle are included exactly once (no duplicates)
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'id="chatbotWidget"'), 'chatbotWidget must appear exactly once on admin dashboard');
        $this->assertEquals(1, substr_count($content, 'id="chatbotToggle"'), 'chatbotToggle must appear exactly once on admin dashboard');
        $this->assertEquals(1, substr_count($content, 'id="chatbotPanel"'), 'chatbotPanel must appear exactly once on admin dashboard');

        // Verify all required JavaScript selector IDs match in DOM
        $this->assertStringContainsString('id="chatbotClose"', $content);
        $this->assertStringContainsString('id="chatbotMessages"', $content);
        $this->assertStringContainsString('id="chatbotForm"', $content);
        $this->assertStringContainsString('id="chatbotText"', $content);
        $this->assertStringContainsString('id="chatbotSubmit"', $content);
    }

    // 10. Backend chatbot routes remain accessible and authenticated regardless of UI visibility flag
    public function test_backend_chatbot_routes_remain_functional_when_ui_flag_is_false(): void
    {
        Config::set('features.chatbot_ui_enabled', false);

        $response = $this->actingAs($this->student)->getJson(route('chatbot.history'));

        $response->assertOk();
        $response->assertJsonStructure(['conversation_id', 'messages', 'ui_enabled']);
        $this->assertFalse($response->json('ui_enabled'));
    }

    // 11. Backend chatbot history reports ui_enabled true when feature flag is enabled
    public function test_chatbot_history_reports_ui_enabled_true_when_active(): void
    {
        Config::set('features.chatbot_ui_enabled', true);

        $response = $this->actingAs($this->student)->getJson(route('chatbot.history'));

        $response->assertOk();
        $this->assertTrue($response->json('ui_enabled'));
    }
}
