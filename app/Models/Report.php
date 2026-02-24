<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'admin_response',
        'resolved_at',
        'response_read'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function markAsResolved(?string $adminResponse = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_response' => $adminResponse,
            'resolved_at' => now(),
        ]);
    }
}
