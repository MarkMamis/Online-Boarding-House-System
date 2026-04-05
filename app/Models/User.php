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
        'is_active',
        'profile_image_path',
        'school_id_path',
        'enrollment_proof_type',
        'enrollment_proof_path',
        'school_id_verification_status',
        'school_id_verified_at',
        'school_id_verified_by',
        'school_id_rejection_reason',
        'student_id',
        'course',
        'gender',
        'year_level',
        'birth_date',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_contact_relationship',
        'parent_contact_name',
        'parent_contact_number',
        'parent_contact_address',
        'parent_contact_photo_path',
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
            'is_active' => 'boolean',
            'school_id_verified_at' => 'datetime',
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

    public function isStudentSetupComplete(): bool
    {
        if ($this->role !== 'student') {
            return true;
        }

        return count($this->missingStudentSetupFields()) === 0;
    }

    public function missingStudentSetupFields(): array
    {
        $isFirstYear = (string) $this->year_level === '1st Year';

        $requiredFields = [
            'full_name' => 'Full name',
            'profile_image_path' => 'Profile photo',
            'contact_number' => 'Contact number',
            'course' => 'Course',
            'year_level' => 'Year level',
            'gender' => 'Gender',
            'address' => 'Home address',
            'emergency_contact_name' => 'Emergency contact name',
            'emergency_contact_number' => 'Emergency contact number',
            'emergency_contact_relationship' => 'Emergency contact relationship',
            'parent_contact_name' => 'Parent or guardian name',
            'parent_contact_number' => 'Parent or guardian contact number',
            'parent_contact_address' => 'Parent or guardian address',
        ];

        if (!$isFirstYear) {
            $requiredFields['student_id'] = 'Student ID';
            $requiredFields['school_id_path'] = 'School ID photo';
        } else {
            $requiredFields['enrollment_proof_type'] = 'Enrollment proof type (COR or COE)';
            $requiredFields['enrollment_proof_path'] = 'Enrollment proof file (COR or COE)';
        }

        $missing = [];

        foreach ($requiredFields as $attribute => $label) {
            if (!filled($this->{$attribute})) {
                $missing[] = $label;
            }
        }

        return $missing;
    }
}
