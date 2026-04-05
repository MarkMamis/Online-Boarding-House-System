<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordProfile extends Model
{
    use HasFactory;

    public const DEFAULT_TENANT_PRIVACY_SETTINGS = [
        'show_tenant_email' => true,
        'show_guardian_contact' => false,
        'show_guardian_address' => false,
        'show_guardian_photo' => false,
        'show_emergency_contact' => false,
    ];

    protected $fillable = [
        'user_id',
        'contact_number',
        'boarding_house_name',
        'about',
        'business_permit_path',
        'business_permit_status',
        'business_permit_reviewed_at',
        'business_permit_reviewed_by',
        'business_permit_rejection_reason',
        'profile_completed',
        'billing_completed',
        'payment_bank_name',
        'payment_account_name',
        'payment_account_number',
        'payment_gcash_number',
        'payment_gcash_name',
        'payment_gcash_qr_path',
        'payment_instructions',
        'preferred_payment_methods',
        'tenant_privacy_settings',
    ];

    protected $casts = [
        'preferred_payment_methods' => 'array',
        'business_permit_reviewed_at' => 'datetime',
        'profile_completed' => 'boolean',
        'billing_completed' => 'boolean',
        'tenant_privacy_settings' => 'array',
    ];

    public static function defaultTenantPrivacySettings(): array
    {
        return self::DEFAULT_TENANT_PRIVACY_SETTINGS;
    }

    public function resolvedTenantPrivacySettings(): array
    {
        return array_merge(
            self::defaultTenantPrivacySettings(),
            (array) ($this->tenant_privacy_settings ?? [])
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
