<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\LandlordProfile;
use App\Models\LeaveRequest;
use App\Models\Property;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Transport\ResendTransport;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResendTransactionalEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function createStudentUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Student ' . $id,
            'full_name' => 'Student Full ' . $id,
            'email' => 'student_' . $id . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'contact_number' => '09171234567',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
            'school_id_verification_status' => 'approved',
            'school_id_path' => 'school_ids/' . $id . '.jpg',
            'profile_image_path' => 'profiles/' . $id . '.jpg',
            'student_id' => '2023-100' . rand(10, 99),
            'college' => 'CAS',
            'program' => 'BSCS',
            'year_level' => '3rd Year',
            'birth_date' => '2002-05-10',
            'gender' => 'Male',
            'address' => 'Victoria, Oriental Mindoro',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_number' => '09181234567',
            'emergency_contact_relationship' => 'Mother',
            'parent_contact_name' => 'John Doe',
            'parent_contact_number' => '09189998888',
            'parent_contact_address' => 'Victoria, Oriental Mindoro',
            'enrollment_proof_type' => 'COR',
            'enrollment_proof_path' => 'proofs/' . $id . '.pdf',
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function createLandlordUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Landlord ' . $id,
            'full_name' => 'Landlord Full ' . $id,
            'email' => 'landlord_' . $id . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'landlord',
            'contact_number' => '09177654321',
            'boarding_house_name' => 'Cozy Pines ' . $id,
            'onboarding_complete' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        LandlordProfile::create([
            'user_id' => $user->id,
            'contact_number' => '09177654321',
            'boarding_house_name' => 'Cozy Pines ' . $id,
            'about' => 'Cozy boarding house accommodation for MinSU students.',
            'business_permit_status' => 'approved',
            'business_permit_path' => 'permits/test.pdf',
        ]);

        return $user->refresh();
    }

    protected function createAdminUser(array $attributes = []): User
    {
        $id = uniqid();
        $user = User::create(array_merge([
            'name' => 'Admin ' . $id,
            'full_name' => 'Admin Full ' . $id,
            'email' => 'admin_' . $id . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'contact_number' => '09170000001',
            'boarding_house_name' => 'N/A',
            'onboarding_complete' => true,
        ], $attributes));

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user->refresh();
    }

    protected function createPropertyWithRoom(User $landlord): array
    {
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'MinSU Haven ' . uniqid(),
            'description' => 'Test property description',
            'address' => 'Victoria, Oriental Mindoro',
            'city' => 'Victoria',
            'approval_status' => 'approved',
            'status' => 'approved',
        ]);

        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'capacity' => 2,
            'slots_available' => 2,
            'price' => 2500,
            'status' => 'available',
            'pricing_model' => Room::PRICING_MODEL_PER_BED,
        ]);

        return [$property, $room];
    }

    public function test_resend_mailer_is_configured_and_resolves_resend_transport(): void
    {
        Config::set('services.resend.key', 're_dummy_test_api_key_12345');

        $mailer = app('mail.manager')->mailer('resend');

        $this->assertNotNull($mailer);
        $this->assertInstanceOf(ResendTransport::class, $mailer->getSymfonyTransport());
    }

    public function test_email_verification_notification_dispatches_mail(): void
    {
        Notification::fake();

        $student = $this->createStudentUser(['email_verified_at' => null]);

        $student->sendEmailVerificationNotification();

        Notification::assertSentTo($student, VerifyEmail::class, function ($notification) use ($student) {
            $channels = $notification->via($student);
            return in_array('mail', $channels, true);
        });
    }

    public function test_password_reset_notification_dispatches_mail(): void
    {
        Notification::fake();

        $student = $this->createStudentUser();

        $this->post(route('password.email'), [
            'email' => $student->email,
        ]);

        Notification::assertSentTo($student, ResetPassword::class, function ($notification) use ($student) {
            $channels = $notification->via($student);
            return in_array('mail', $channels, true);
        });
    }

    public function test_system_notification_channels_and_mail_message_generation(): void
    {
        $student = $this->createStudentUser();

        // 1. Without sendEmail: only database channel
        $notificationWithoutMail = new SystemNotification('Test Title', 'Test Message', '/test-url');
        $this->assertEquals(['database'], $notificationWithoutMail->via($student));

        // 2. With sendEmail: includes both database and mail channels
        $notificationWithMail = new SystemNotification(
            title: 'Boarding Request Approved',
            message: 'Your booking has been approved for Room 101.',
            url: '/student/onboarding',
            meta: ['booking_id' => 42],
            sendEmail: true,
            actionText: 'Proceed to Onboarding'
        );

        $channels = $notificationWithMail->via($student);
        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);

        // 3. Verify toMail payload and rendered views
        $mailMessage = $notificationWithMail->toMail($student);
        $this->assertStringContainsString('Boarding Request Approved', $mailMessage->subject);
        $this->assertEquals('emails.transactional', $mailMessage->view['html'] ?? $mailMessage->view);
        $this->assertEquals('emails.transactional-text', $mailMessage->view['text'] ?? $mailMessage->view);

        $renderedHtml = view($mailMessage->view['html'], $mailMessage->viewData)->render();
        $this->assertStringContainsString('Boarding Request Approved', $renderedHtml);
        $this->assertStringContainsString('Your booking has been approved for Room 101.', $renderedHtml);
        $this->assertStringContainsString('Proceed to Onboarding', $renderedHtml);
        $this->assertStringContainsString('Mindoro State University', $renderedHtml);

        $renderedText = view($mailMessage->view['text'], $mailMessage->viewData)->render();
        $this->assertStringContainsString('Boarding Request Approved', $renderedText);
        $this->assertStringContainsString('Your booking has been approved for Room 101.', $renderedText);
    }

    public function test_booking_request_dispatches_transactional_email_to_landlord(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $response = $this->actingAs($student)->post(route('bookings.store', $room), [
            'check_in' => now()->addDays(2)->format('Y-m-d'),
            'check_out' => now()->addMonths(3)->format('Y-m-d'),
            'occupancy_mode' => 'shared',
        ]);

        $response->assertRedirect();

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            $channels = $notification->via($landlord);
            return in_array('mail', $channels, true)
                && in_array('database', $channels, true)
                && str_contains($notification->title, 'New boarding request');
        });
    }

    public function test_booking_approval_dispatches_transactional_email_to_student(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(1),
            'check_out' => now()->addMonths(2),
            'status' => 'pending',
            'occupancy_mode' => 'shared',
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.bookings.approve', $booking));
        $response->assertRedirect();

        $this->assertEquals('approved', $booking->fresh()->status);

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            $channels = $notification->via($student);
            return in_array('mail', $channels, true)
                && in_array('database', $channels, true)
                && str_contains($notification->title, 'Boarding request approved');
        });
    }

    public function test_booking_rejection_dispatches_transactional_email_to_student(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(1),
            'check_out' => now()->addMonths(2),
            'status' => 'pending',
            'occupancy_mode' => 'shared',
        ]);

        $response = $this->actingAs($landlord)->post(route('landlord.bookings.reject', $booking));
        $response->assertRedirect();

        $this->assertEquals('rejected', $booking->fresh()->status);

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            $channels = $notification->via($student);
            return in_array('mail', $channels, true)
                && in_array('database', $channels, true)
                && str_contains($notification->title, 'Boarding request rejected');
        });
    }

    public function test_property_approval_and_rejection_dispatch_transactional_emails(): void
    {
        Notification::fake();

        $admin = $this->createAdminUser();
        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $property->update(['approval_status' => 'pending']);

        // 1. Approve
        $response = $this->actingAs($admin)->post(route('admin.properties.approve', $property));
        $response->assertRedirect();

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            return in_array('mail', $notification->via($landlord), true)
                && str_contains($notification->title, 'Property approved');
        });

        // 2. Reject
        $response = $this->actingAs($admin)->post(route('admin.properties.reject', $property), [
            'rejection_reason' => 'Missing clear building exterior photo.',
        ]);
        $response->assertRedirect();

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            return in_array('mail', $notification->via($landlord), true)
                && str_contains($notification->title, 'Property rejected')
                && str_contains($notification->message, 'Missing clear building exterior photo.');
        });
    }

    public function test_tenant_onboarding_step_notifications_dispatch_transactional_emails(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->addDays(1),
            'check_out' => now()->addMonths(2),
            'status' => 'approved',
            'occupancy_mode' => 'shared',
        ]);

        $onboarding = TenantOnboarding::create([
            'booking_id' => $booking->id,
            'deposit_amount' => 2500,
            'status' => 'documents_uploaded',
            'required_documents' => ['student_id', 'proof_of_income'],
        ]);

        // Student signs contract
        $response = $this->actingAs($student)->post(route('student.onboarding.sign_contract', $onboarding), [
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);
        $response->assertRedirect();

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            return in_array('mail', $notification->via($landlord), true)
                && str_contains($notification->title, 'Onboarding contract signed');
        });
    }

    public function test_payment_reminder_and_confirmation_dispatch_transactional_emails(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->subMonth(),
            'check_out' => now()->addMonths(2),
            'status' => 'approved',
            'occupancy_mode' => 'shared',
            'payment_status' => 'pending',
        ]);

        // 1. Send payment reminder
        $response = $this->actingAs($landlord)->post(route('landlord.payments.remind', $booking->id));
        $response->assertRedirect();

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            return in_array('mail', $notification->via($student), true)
                && str_contains($notification->title, 'Payment reminder');
        });

        // 2. Mark as paid
        $response = $this->actingAs($landlord)->post(route('landlord.payments.mark_paid', $booking->id));
        $response->assertRedirect();

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            return in_array('mail', $notification->via($student), true)
                && str_contains($notification->title, 'Rent payment confirmed');
        });
    }

    public function test_student_and_landlord_verification_outcomes_dispatch_transactional_emails(): void
    {
        Notification::fake();

        $admin = $this->createAdminUser();
        $student = $this->createStudentUser([
            'school_id_verification_status' => 'pending',
            'school_id_path' => 'school_ids/test.jpg',
        ]);

        // Admin approves student verification
        $this->actingAs($admin)->post(route('admin.student_verifications.approve', $student));

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            return in_array('mail', $notification->via($student), true)
                && str_contains($notification->title, 'Academic verification approved');
        });

        // Admin approves landlord permit
        $landlord = $this->createLandlordUser();
        $this->actingAs($admin)->post(route('admin.permits.approve', $landlord));

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            return in_array('mail', $notification->via($landlord), true)
                && str_contains($notification->title, 'Business permit approved');
        });
    }

    public function test_leave_request_workflow_dispatches_transactional_emails(): void
    {
        Notification::fake();

        $landlord = $this->createLandlordUser();
        [$property, $room] = $this->createPropertyWithRoom($landlord);
        $student = $this->createStudentUser();

        $booking = Booking::create([
            'student_id' => $student->id,
            'room_id' => $room->id,
            'check_in' => now()->subMonth(),
            'check_out' => now()->addMonths(2),
            'status' => 'approved',
            'occupancy_mode' => 'shared',
        ]);

        TenantOnboarding::create([
            'booking_id' => $booking->id,
            'status' => 'completed',
        ]);

        // Student submits leave request
        $response = $this->actingAs($student)->post(route('student.leave_requests.store'), [
            'booking_id' => $booking->id,
            'leave_date' => now()->addDays(15)->format('Y-m-d'),
            'reason' => 'End of academic semester.',
        ]);
        $response->assertRedirect();

        Notification::assertSentTo($landlord, SystemNotification::class, function ($notification) use ($landlord) {
            return in_array('mail', $notification->via($landlord), true)
                && str_contains($notification->title, 'Leave request submitted');
        });

        $leaveRequest = LeaveRequest::first();

        // Landlord approves leave request
        $this->actingAs($landlord)->post(route('landlord.leave_requests.approve', $leaveRequest));

        Notification::assertSentTo($student, SystemNotification::class, function ($notification) use ($student) {
            return in_array('mail', $notification->via($student), true)
                && str_contains($notification->title, 'Leave request approved');
        });
    }

    public function test_mail_test_command_execution_and_validation(): void
    {
        // 1. Invalid email rejected
        $this->artisan('obhs:mail-test', ['email' => 'not-an-email'])
            ->assertExitCode(1);

        // 2. Valid email dispatches via log mailer successfully
        $this->artisan('obhs:mail-test', [
            'email' => 'developer@example.com',
            '--mailer' => 'log',
        ])
            ->expectsOutputToContain('Preparing to send test email:')
            ->expectsOutputToContain('Test email dispatched successfully via [log].')
            ->assertExitCode(0);
    }
}
