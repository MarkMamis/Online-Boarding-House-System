<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'image_path',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'average_rating',
        'ratings_count',
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
        'price_min',
        'price_max',
    ];

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function refreshPriceRange(): void
    {
        $aggregates = $this->rooms()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        $this->forceFill([
            'price_min' => $aggregates?->min_price,
            'price_max' => $aggregates?->max_price,
        ])->save();
    }
}
