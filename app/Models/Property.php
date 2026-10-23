<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'landlord_id',
        'image_path',
        'building_inclusions',
        'house_rules',
        'approval_status',
        'submitted_at',
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

    protected $casts = [
        'building_inclusions' => 'array',
        'house_rules' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function getMissingSubmissionRequirements(): array
    {
        $missing = [];

        if (! filled(trim((string) $this->name))) {
            $missing['name'] = [
                'field' => 'name',
                'label' => 'Property Name',
                'panel' => 0,
                'message' => 'Property name is required.',
            ];
        }

        if (! filled(trim((string) $this->address))) {
            $missing['address'] = [
                'field' => 'address',
                'label' => 'Street Address',
                'panel' => 0,
                'message' => 'Property address is required.',
            ];
        }

        if (! filled(trim((string) $this->description))) {
            $missing['description'] = [
                'field' => 'description',
                'label' => 'Description',
                'panel' => 0,
                'message' => 'A property description is required before submission.',
            ];
        }

        if (empty($this->image_path)) {
            $missing['image'] = [
                'field' => 'image',
                'label' => 'Cover Photo',
                'panel' => 0,
                'message' => 'A cover photo is required before submission.',
            ];
        }

        if (empty($this->latitude) || empty($this->longitude)) {
            $missing['location'] = [
                'field' => 'location',
                'label' => 'Map Location',
                'panel' => 2,
                'message' => 'Property map coordinates must be set.',
            ];
        }

        return $missing;
    }

    public function isCompleteForSubmission(): bool
    {
        return empty($this->getMissingSubmissionRequirements());
    }

    public function isDraft(): bool
    {
        return ($this->approval_status ?? 'draft') === 'draft';
    }

    public function isPending(): bool
    {
        return ($this->approval_status ?? '') === 'pending';
    }

    public function isApproved(): bool
    {
        return ($this->approval_status ?? '') === 'approved';
    }

    public function isRejected(): bool
    {
        return ($this->approval_status ?? '') === 'rejected';
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function scopeVisibleToAudience(Builder $query): Builder
    {
        if (! Schema::hasColumn('landlord_profiles', 'business_permit_status')
            || ! Schema::hasColumn('landlord_profiles', 'billing_completed')) {
            return $query->where('approval_status', 'approved');
        }

        return $query
            ->where('approval_status', 'approved')
            ->whereHas('landlord.landlordProfile', function (Builder $profileQuery) {
                $profileQuery
                    ->where('business_permit_status', 'approved')
                    ->where('billing_completed', true);
            });
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
