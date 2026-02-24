<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property \Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property \Illuminate\Notifications\DatabaseNotificationCollection $readNotifications
 * @property \Illuminate\Notifications\DatabaseNotificationCollection $unreadNotifications
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany notifications()
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany readNotifications()
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany unreadNotifications()
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use MustVerifyEmailTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'full_name',
        'email',
        'password',
        'contact_number',
        'boarding_house_name',
        'role',
        'profile_image_path',
        'student_id',
        'course',
        'year_level',
        'birth_date',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',
        'guardian_name',
        'guardian_contact',
        'blood_type',
        'allergies',
        'medications',
        'medical_conditions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function landlordProfile()
    {
        return $this->hasOne(LandlordProfile::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}
