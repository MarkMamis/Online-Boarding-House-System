<?php

namespace Tests\Feature;

use App\Models\LandlordProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LandlordPhotoUploadOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $landlord;

    protected User $otherLandlord;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('r2');
        Storage::fake('supabase');

        $this->landlord = $this->createLandlord('Landlord One');
        $this->otherLandlord = $this->createLandlord('Landlord Two');

        $this->property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Sunrise Villa',
            'description' => 'A comfortable student boarding house',
            'address' => '123 University Avenue, Calapan City',
            'latitude' => 13.4115,
            'longitude' => 121.1803,
            'approval_status' => 'approved',
            'status' => 'approved',
        ]);
    }

    protected function createLandlord(string $name): User
    {
        $id = uniqid();
        $user = User::create([
            'name' => $name,
            'full_name' => $name.' Full',
            'email' => 'lnd_'.$id.'@example.com',
            'password' => bcrypt('password'),
            'role' => 'landlord',
            'contact_number' => '0918000'.rand(1000, 9999),
            'boarding_house_name' => $name.' House',
            'onboarding_complete' => true,
            'address' => 'Calapan City',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'boarding_house_name' => $name.' House',
            'about' => 'Clean boarding house',
            'business_permit_status' => 'approved',
            'contact_number' => $user->contact_number,
        ]);

        return $user->refresh();
    }

    // 1. JPEG below 2 MB
    public function test_room_creation_accepts_jpeg_below_2mb(): void
    {
        $file = UploadedFile::fake()->image('room-small.jpg', 800, 600)->size(1500); // 1.5 MB

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '101',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $response->assertSessionHas('success');

        $room = Room::where('room_number', '101')->where('property_id', $this->property->id)->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
    }

    // 2. JPEG between 2 MB and 5 MB (Fixes the production bug!)
    public function test_room_creation_accepts_jpeg_between_2mb_and_5mb(): void
    {
        $file = UploadedFile::fake()->image('camera-photo.jpg', 1600, 1200)->size(3500); // 3.5 MB

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '102',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $response->assertSessionHas('success');

        $room = Room::where('room_number', '102')->where('property_id', $this->property->id)->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
    }

    // 3. PNG within 5 MB
    public function test_room_creation_accepts_png_within_5mb(): void
    {
        $file = UploadedFile::fake()->image('room-floorplan.png', 1000, 800)->size(4000); // 4 MB

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '103',
                'capacity' => 1,
                'status' => 'available',
                'pricing_model' => 'per_room',
                'price_per_room' => 4500,
                'image' => $file,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $room = Room::where('room_number', '103')->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
    }

    // 4. WebP within 5 MB
    public function test_room_creation_accepts_webp_within_5mb(): void
    {
        $file = UploadedFile::fake()->image('modern-room.webp', 1200, 900)->size(2800);

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '104',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 3000,
                'image' => $file,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $room = Room::where('room_number', '104')->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
    }

    // 5. Image exceeding 5 MB rejected with friendly error message
    public function test_room_creation_rejects_image_exceeding_5mb_with_friendly_message(): void
    {
        $file = UploadedFile::fake()->image('huge-dslr-photo.jpg', 4000, 3000)->size(6500); // 6.5 MB

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '105',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $error = session('errors')->first('image');
        $this->assertSame('This photo is too large. Please choose one under 5 MB.', $error);
    }

    // 6. Invalid MIME content
    public function test_room_creation_rejects_invalid_mime_content(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '106',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $error = session('errors')->first('image');
        $this->assertSame('Please select a valid image file (JPG, PNG, or WebP).', $error);
    }

    // 7. Malformed / corrupted image
    public function test_room_creation_rejects_malformed_image(): void
    {
        $file = UploadedFile::fake()->create('corrupted.jpg', 100, 'text/plain');

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '107',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors(['image']);
    }

    // 8. Unsupported format (e.g. SVG or executable)
    public function test_room_creation_rejects_unsupported_format(): void
    {
        $file = UploadedFile::fake()->create('vector.svg', 100, 'image/svg+xml');

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '108',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $file,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $error = session('errors')->first('image');
        $this->assertSame('Please select a valid image file (JPG, PNG, or WebP).', $error);
    }

    // 9. Missing optional image succeeds
    public function test_room_creation_succeeds_without_image(): void
    {
        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '109',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $room = Room::where('room_number', '109')->first();
        $this->assertNotNull($room);
        $this->assertNull($room->image_path);
    }

    // 10. Additional room photo arrays (detail_images) within 5MB
    public function test_room_creation_accepts_additional_detail_images_within_5mb(): void
    {
        $cover = UploadedFile::fake()->image('cover.jpg', 800, 600)->size(1500);
        $detail1 = UploadedFile::fake()->image('bathroom.jpg', 800, 600)->size(2200);
        $detail2 = UploadedFile::fake()->image('window.jpg', 800, 600)->size(3100);

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '110',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $cover,
                'detail_images' => [$detail1, $detail2],
                'detail_labels' => ['Bathroom', 'Window View'],
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $room = Room::where('room_number', '110')->first();
        $this->assertNotNull($room);
        $this->assertCount(2, $room->roomImages);
    }

    // 11. Exact size boundaries (5120 KB allowed, 5121 KB rejected)
    public function test_exact_5120kb_boundary(): void
    {
        $allowed = UploadedFile::fake()->image('boundary-pass.jpg', 800, 600)->size(5120);
        $responseAllowed = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '111',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $allowed,
            ]
        );
        $responseAllowed->assertRedirect(route('landlord.properties.rooms.index', $this->property->id));
        $this->assertDatabaseHas('rooms', ['room_number' => '111']);

        $exceeded = UploadedFile::fake()->image('boundary-fail.jpg', 800, 600)->size(5121);
        $responseExceeded = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '112',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2500,
                'image' => $exceeded,
            ]
        );
        $responseExceeded->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('rooms', ['room_number' => '112']);
    }

    // 12. Property creation upload
    public function test_property_creation_accepts_image_up_to_5mb(): void
    {
        $image = UploadedFile::fake()->image('property-main.jpg', 1600, 1200)->size(4200); // 4.2 MB

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.store'),
            [
                'name' => 'Hillside Haven',
                'address' => '456 Campus Drive',
                'description' => 'Quiet place for students',
                'latitude' => 13.4120,
                'longitude' => 121.1810,
                'image' => $image,
            ]
        );

        $property = Property::where('name', 'Hillside Haven')->first();
        $this->assertNotNull($property);
        $response->assertRedirect(route('landlord.properties.show', $property->id));
        $this->assertNotNull($property->image_path);
    }

    // 13. Property creation rejects image exceeding 5MB with friendly error
    public function test_property_creation_rejects_image_exceeding_5mb(): void
    {
        $image = UploadedFile::fake()->image('large-property.jpg', 2500, 2000)->size(6200);

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.store'),
            [
                'name' => 'Too Large Villa',
                'address' => '789 Campus Drive',
                'image' => $image,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $error = session('errors')->first('image');
        $this->assertSame('This photo is too large. Please choose one under 5 MB.', $error);
    }

    // 14. Property editing replacement
    public function test_property_editing_replaces_photo(): void
    {
        $oldImage = UploadedFile::fake()->image('old-facade.jpg', 800, 600)->size(1000);
        $newImage = UploadedFile::fake()->image('new-facade.jpg', 1600, 1200)->size(3800);

        // Set initial photo
        $this->actingAs($this->landlord)->put(
            route('landlord.properties.update', $this->property->id),
            [
                'name' => $this->property->name,
                'address' => $this->property->address,
                'image' => $oldImage,
            ]
        );
        $this->property->refresh();
        $oldPath = $this->property->image_path;
        $this->assertNotNull($oldPath);

        // Replace photo
        $response = $this->actingAs($this->landlord)->put(
            route('landlord.properties.update', $this->property->id),
            [
                'name' => $this->property->name,
                'address' => $this->property->address,
                'image' => $newImage,
            ]
        );

        $response->assertRedirect(route('landlord.properties.show', $this->property->id));
        $this->property->refresh();
        $this->assertNotSame($oldPath, $this->property->image_path);
    }

    // 15. Property editing retains existing photo when unchanged
    public function test_property_editing_retains_existing_photo_when_image_not_provided(): void
    {
        $initialImage = UploadedFile::fake()->image('initial.jpg', 800, 600)->size(1200);
        $this->actingAs($this->landlord)->put(
            route('landlord.properties.update', $this->property->id),
            [
                'name' => $this->property->name,
                'address' => $this->property->address,
                'image' => $initialImage,
            ]
        );
        $this->property->refresh();
        $savedPath = $this->property->image_path;

        // Update without image field
        $this->actingAs($this->landlord)->put(
            route('landlord.properties.update', $this->property->id),
            [
                'name' => 'Updated Property Name',
                'address' => $this->property->address,
            ]
        );

        $this->property->refresh();
        $this->assertSame('Updated Property Name', $this->property->name);
        $this->assertSame($savedPath, $this->property->image_path);
    }

    // 16. Room editing replacement
    public function test_room_editing_replaces_photo(): void
    {
        $room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => '201',
            'capacity' => 2,
            'slots_available' => 2,
            'status' => 'available',
            'price' => 2000,
        ]);

        $replacementImage = UploadedFile::fake()->image('room-replacement.jpg', 1600, 1200)->size(3200);

        $response = $this->actingAs($this->landlord)->put(
            route('landlord.properties.rooms.update', [$this->property->id, $room->id]),
            [
                'room_number' => '201',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
                'image' => $replacementImage,
            ]
        );

        $response->assertRedirect(route('landlord.properties.rooms.edit', [$this->property->id, $room->id]));
        $room->refresh();
        $this->assertNotNull($room->image_path);
    }

    // 17. Room editing retains existing photo when unchanged
    public function test_room_editing_retains_existing_photo_when_unchanged(): void
    {
        $room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => '202',
            'capacity' => 2,
            'slots_available' => 2,
            'status' => 'available',
            'price' => 2000,
            'image_path' => 'rooms/existing/photo.jpg',
        ]);

        $this->actingAs($this->landlord)->put(
            route('landlord.properties.rooms.update', [$this->property->id, $room->id]),
            [
                'room_number' => '202',
                'capacity' => 3,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
            ]
        );

        $room->refresh();
        $this->assertSame(3, $room->capacity);
        $this->assertSame('rooms/existing/photo.jpg', $room->image_path);
    }

    // 18. Unauthorized landlord upload rejected (Cross-property ownership)
    public function test_unauthorized_landlord_cannot_upload_to_another_landlords_property(): void
    {
        $image = UploadedFile::fake()->image('hacker.jpg', 800, 600)->size(1000);

        $response = $this->actingAs($this->otherLandlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '301',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
                'image' => $image,
            ]
        );

        $response->assertNotFound();
        $this->assertDatabaseMissing('rooms', ['room_number' => '301']);
    }

    // 19. Unauthorized landlord cannot update another landlord's room
    public function test_unauthorized_landlord_cannot_update_another_landlords_room(): void
    {
        $room = Room::create([
            'property_id' => $this->property->id,
            'room_number' => '302',
            'capacity' => 2,
            'slots_available' => 2,
            'status' => 'available',
            'price' => 2000,
        ]);

        $image = UploadedFile::fake()->image('hacker2.jpg', 800, 600)->size(1000);

        $response = $this->actingAs($this->otherLandlord)->put(
            route('landlord.properties.rooms.update', [$this->property->id, $room->id]),
            [
                'room_number' => '302',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
                'image' => $image,
            ]
        );

        $response->assertNotFound();
    }

    // 20. Property draft autosave handles image up to 5MB
    public function test_property_draft_autosave_accepts_image_up_to_5mb(): void
    {
        $image = UploadedFile::fake()->image('draft-camera.jpg', 1600, 1200)->size(3600); // 3.6 MB

        $response = $this->actingAs($this->landlord)->postJson(
            route('landlord.properties.autosave'),
            [
                'name' => 'Draft Auto House',
                'address' => 'Draft Address',
                'image' => $image,
            ]
        );

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'saved' => true,
        ]);

        $propId = $response->json('property_id');
        $this->assertNotNull($propId);
        $property = Property::find($propId);
        $this->assertSame('draft', $property->approval_status);
        $this->assertNotNull($property->image_path);
    }

    // 21. Property draft autosave rejects image exceeding 5MB
    public function test_property_draft_autosave_rejects_image_exceeding_5mb(): void
    {
        $image = UploadedFile::fake()->image('huge-draft.jpg', 3000, 2000)->size(6000);

        $response = $this->actingAs($this->landlord)->postJson(
            route('landlord.properties.autosave'),
            [
                'name' => 'Draft Huge House',
                'address' => 'Draft Address',
                'image' => $image,
            ]
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
        $error = $response->json('errors.image.0');
        $this->assertSame('This photo is too large. Please choose one under 5 MB.', $error);
    }

    // 22. Strict security: no arbitrary file execution disguised as image
    public function test_no_arbitrary_file_execution(): void
    {
        $dangerousFile = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '401',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
                'image' => $dangerousFile,
            ]
        );

        $response->assertSessionHasErrors(['image']);
        $this->assertDatabaseMissing('rooms', ['room_number' => '401']);
    }

    // 23. Detail images count limit (max:10)
    public function test_room_creation_rejects_more_than_10_detail_images(): void
    {
        $detailImages = [];
        for ($i = 0; $i < 11; $i++) {
            $detailImages[] = UploadedFile::fake()->image("img_{$i}.jpg", 400, 300)->size(500);
        }

        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [
                'room_number' => '501',
                'capacity' => 2,
                'status' => 'available',
                'pricing_model' => 'per_bed',
                'price_per_bed' => 2000,
                'detail_images' => $detailImages,
            ]
        );

        $response->assertSessionHasErrors(['detail_images']);
        $error = session('errors')->first('detail_images');
        $this->assertSame('You can upload a maximum of 10 additional photos at once.', $error);
    }

    // 24. PostTooLargeException returns friendly error
    public function test_post_too_large_exception_handling(): void
    {
        $response = $this->actingAs($this->landlord)->post(
            route('landlord.properties.rooms.store', $this->property->id),
            [],
            ['Content-Length' => 999999999] // Simulated huge post
        );

        // Or directly invoking a route that throws PostTooLargeException
        $webResponse = $this->actingAs($this->landlord)->get('/');
        // Test exception rendering directly via app
        $request = \Illuminate\Http\Request::create('/landlord/properties', 'POST');
        $request->setUserResolver(fn () => $this->landlord);
        $exception = new \Illuminate\Http\Exceptions\PostTooLargeException;

        // Render through app
        $handler = app(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $renderedWeb = $handler->render($request, $exception);
        $this->assertTrue($renderedWeb->isRedirection());

        $jsonRequest = \Illuminate\Http\Request::create('/landlord/properties/autosave', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $renderedJson = $handler->render($jsonRequest, $exception);
        $this->assertSame(413, $renderedJson->getStatusCode());
        $this->assertStringContainsString('Your upload is too large. Try uploading fewer photos at once.', (string) $renderedJson->getContent());
    }
}
