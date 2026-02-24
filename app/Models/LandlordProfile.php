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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
