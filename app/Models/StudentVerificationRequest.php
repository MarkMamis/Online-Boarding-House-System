<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentVerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'request_student_id',
        'request_school_id_photo',
        'instructions',
        'status',
        'requested_by',
        'requested_at',
        'resubmitted_at',
        'resolved_by',
        'resolved_at',
        'admin_notes',
    ];

    protected $casts = [
        'request_student_id' => 'boolean',
        'request_school_id_photo' => 'boolean',
        'requested_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'resubmitted']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isResubmitted(): bool
    {
        return $this->status === 'resubmitted';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
