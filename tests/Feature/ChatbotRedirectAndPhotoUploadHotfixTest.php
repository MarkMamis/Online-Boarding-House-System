<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\AI\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChatbotRedirectAndPhotoUploadHotfixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('r2');
        Storage::fake('supabase');
    }

    protected function createVerifiedUser(string $role, array $extra = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => ucfirst($role).' '.$id,
            'full_name' => ucfirst($role).' User '.$id,
            'email' => $role.'_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => $role,
            'contact_number' => '0917'.rand(1000000, 9999999),
            'address' => 'Calapan City, Oriental Mindoro',
            'boarding_house_name' => 'BH '.$id,
            'onboarding_complete' => true,
        ], $extra));

        $user->forceFill(['email_verified_at' => now()])->save();

        if ($role === 'landlord') {
            LandlordProfile::create([
                'user_id' => $user->id,
                'boarding_house_name' => 'Test BH '.$id,
                'about' => 'Reliable student housing near MinSU.',
                'contact_number' => $user->contact_number,
                'business_permit_status' => 'approved',
                'preferred_payment_methods' => ['cash'],
                'profile_completed' => true,
                'billing_completed' => true,
            ]);
        }

        if ($role === 'student') {
            $user->forceFill([
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
        }

        return $user->refresh();
    }

    // 1. Authenticated landlord with completed setup can GET history (200 JSON, no 302 redirect)
    public function test_authenticated_landlord_can_get_chatbot_history_as_json(): void
    {
        $landlord = $this->createVerifiedUser('landlord');

        $response = $this->actingAs($landlord)->getJson(route('chatbot.history'));

        $response->assertOk();
        $response->assertJsonStructure([
            'conversation_id',
            'messages',
        ]);
        $response->assertHeader('content-type', 'application/json');
    }

    // 2. Authenticated landlord can POST message (200 JSON, expected schema)
    public function test_authenticated_landlord_can_post_chatbot_message_as_json(): void
    {
        $landlord = $this->createVerifiedUser('landlord');

        $this->mock(AiAssistantService::class, function ($mock) {
            $mock->shouldReceive('handleMessage')
                ->once()
                ->andReturn([
                    'reply' => 'Kumusta! May available rooms po sa Calapan.',
                    'meta' => [
                        'actions' => [
                            [
                                'type' => 'internal_route',
                                'label' => 'View Properties',
                                'route' => '/landlord/properties',
                                'url' => '/landlord/properties',
                            ],
                        ],
                    ],
                ]);
        });

        $response = $this->actingAs($landlord)->postJson(route('chatbot.message'), [
            'content' => 'Show me my properties',
        ]);

        $response->assertOk();
        $response->assertJson([
            'reply' => 'Kumusta! May available rooms po sa Calapan.',
            'message' => 'Kumusta! May available rooms po sa Calapan.',
            'actions' => [
                [
                    'type' => 'internal_route',
                    'label' => 'View Properties',
                    'route' => '/landlord/properties',
                ],
            ],
        ]);
    }

    // 3. Landlord with completed profile in DB but onboarding_complete = false is auto-healed without 302 redirect loop
    public function test_landlord_with_completed_profile_is_auto_healed_on_chatbot_request(): void
    {
        $landlord = $this->createVerifiedUser('landlord', ['onboarding_complete' => false]);
        $this->assertFalse((bool) $landlord->onboarding_complete);

        $response = $this->actingAs($landlord)->getJson(route('chatbot.history'));

        $response->assertOk();
        $this->assertTrue((bool) $landlord->fresh()->onboarding_complete);
    }

    // 4. Incomplete landlord setup returns 403 JSON for JSON requests
    public function test_incomplete_landlord_setup_returns_403_json_for_ajax(): void
    {
        $id = uniqid();
        $incompleteLandlord = User::create([
            'name' => 'Incomplete '.$id,
            'full_name' => 'Incomplete Landlord '.$id,
            'email' => 'inc_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '09170009999',
            'boarding_house_name' => 'Pending BH '.$id,
            'onboarding_complete' => false,
        ]);
        $incompleteLandlord->forceFill(['email_verified_at' => now()])->save();

        $response = $this->actingAs($incompleteLandlord)->getJson(route('chatbot.history'));

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Complete your landlord setup before accessing this feature.',
            'redirect' => route('landlord.setup.show'),
        ]);
    }

    // 5. Landlord setup show method auto-persists onboarding_complete if setup is submitted
    public function test_landlord_setup_show_heals_onboarding_complete_for_completed_accounts(): void
    {
        $landlord = $this->createVerifiedUser('landlord', ['onboarding_complete' => false]);
        $this->assertFalse((bool) $landlord->onboarding_complete);

        $response = $this->actingAs($landlord)->get(route('landlord.setup.show'));

        $response->assertRedirect(route('landlord.dashboard'));
        $this->assertTrue((bool) $landlord->fresh()->onboarding_complete);
    }

    // 6. Student can access chatbot
    public function test_authenticated_student_can_access_chatbot(): void
    {
        $student = $this->createVerifiedUser('student');

        $response = $this->actingAs($student)->getJson(route('chatbot.history'));
        $response->assertOk();
        $response->assertJsonStructure(['conversation_id', 'messages']);
    }

    // 7. Admin can access chatbot
    public function test_authenticated_admin_can_access_chatbot(): void
    {
        $admin = $this->createVerifiedUser('admin');

        $response = $this->actingAs($admin)->getJson(route('chatbot.history'));
        $response->assertOk();
        $response->assertJsonStructure(['conversation_id', 'messages']);
    }

    // 8. Unauthenticated requests return 401 JSON
    public function test_unauthenticated_request_returns_401_json(): void
    {
        $response = $this->getJson(route('chatbot.history'));

        $response->assertStatus(401);
    }

    // 9. Validation failure returns 422 JSON
    public function test_chatbot_message_validation_failure_returns_422(): void
    {
        $landlord = $this->createVerifiedUser('landlord');

        $response = $this->actingAs($landlord)->postJson(route('chatbot.message'), [
            'content' => '', // Empty content
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['content']);
    }

    // 10. AI service failure in controller returns graceful 500 JSON without crashing or leaking sensitive info
    public function test_ai_service_failure_returns_graceful_500_json(): void
    {
        $landlord = $this->createVerifiedUser('landlord');

        $this->mock(AiAssistantService::class, function ($mock) {
            $mock->shouldReceive('handleMessage')
                ->once()
                ->andThrow(new \RuntimeException('External API timeout'));
        });

        $response = $this->actingAs($landlord)->postJson(route('chatbot.message'), [
            'content' => 'Hello assistant',
        ]);

        $response->assertStatus(500);
        $response->assertJsonStructure(['message', 'reply']);
        $this->assertStringNotContainsString('External API timeout', (string) $response->getContent());
    }

    // 11. Conversation history is scoped to owner
    public function test_chatbot_history_is_isolated_per_user(): void
    {
        $landlord1 = $this->createVerifiedUser('landlord');
        $landlord2 = $this->createVerifiedUser('landlord');

        $this->actingAs($landlord1)->getJson(route('chatbot.history'));
        $history1 = $this->actingAs($landlord1)->getJson(route('chatbot.history'))->json('conversation_id');

        $this->actingAs($landlord2)->getJson(route('chatbot.history'));
        $history2 = $this->actingAs($landlord2)->getJson(route('chatbot.history'))->json('conversation_id');

        $this->assertNotEquals($history1, $history2);
    }

    // 12. Landlord room upload accepts 3.5 MB photo (above 2048 KB, under 5120 KB)
    public function test_room_upload_accepts_photo_between_2mb_and_5mb(): void
    {
        $landlord = $this->createVerifiedUser('landlord');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Green Valley Villa',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->image('phone-camera-room.jpg', 1800, 1200)->size(3500); // 3.5 MB

        $response = $this->actingAs($landlord)->post(
            route('landlord.properties.rooms.store', $property->id),
            [
                'room_number' => 'R-301',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2200,
                'image' => $file,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $property->id));
        $room = Room::where('room_number', 'R-301')->where('property_id', $property->id)->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
    }

    // 13. Landlord room upload rejects image above 5 MB with friendly error
    public function test_room_upload_rejects_photo_above_5mb_with_friendly_error(): void
    {
        $landlord = $this->createVerifiedUser('landlord');
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Pinecrest House',
            'address' => 'Calapan City',
            'approval_status' => 'approved',
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->image('huge-phone-room.jpg', 3000, 2000)->size(5500); // 5.5 MB

        $response = $this->actingAs($landlord)->post(
            route('landlord.properties.rooms.store', $property->id),
            [
                'room_number' => 'R-302',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2200,
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $this->assertSame('This photo is too large. Please choose one under 5 MB.', session('errors')->first('image'));
    }

    // 14. Property draft autosave accepts 3.5 MB photo
    public function test_property_draft_autosave_accepts_photo_between_2mb_and_5mb(): void
    {
        $landlord = $this->createVerifiedUser('landlord');
        $file = UploadedFile::fake()->image('phone-facade.jpg', 1800, 1200)->size(3500); // 3.5 MB

        $response = $this->actingAs($landlord)->postJson(route('landlord.properties.autosave'), [
            'name' => 'Auto Saved BH',
            'address' => 'Auto Address',
            'image' => $file,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'saved' => true]);
        $propId = $response->json('property_id');
        $this->assertNotNull($propId);
        $property = Property::find($propId);
        $this->assertNotNull($property->image_path);
    }
}
