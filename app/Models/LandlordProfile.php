<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandlordProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contact_number',
        'boarding_house_name',
        'about',
        'business_permit_path',
        'payment_bank_name',
        'payment_account_name',
        'payment_account_number',
        'payment_gcash_number',
        'payment_gcash_name',
        'payment_gcash_qr_path',
        'payment_instructions',
        'preferred_payment_methods',
    ];

    protected $casts = [
        'preferred_payment_methods' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
