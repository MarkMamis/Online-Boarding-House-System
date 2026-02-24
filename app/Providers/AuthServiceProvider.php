<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Property;
use App\Models\Report;
use App\Models\Room;
use App\Models\TenantOnboarding;
use App\Policies\BookingPolicy;
use App\Policies\MessagePolicy;
use App\Policies\PropertyPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RoomPolicy;
use App\Policies\TenantOnboardingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Property::class => PropertyPolicy::class,
        Room::class => RoomPolicy::class,
        Booking::class => BookingPolicy::class,
        TenantOnboarding::class => TenantOnboardingPolicy::class,
        Report::class => ReportPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    public function boot(): void
    {
        // Policies are registered by the base ServiceProvider.
    }
}
