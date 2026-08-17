<?php

namespace App\Models;

use App\Services\DocumentExpirationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LandlordDocument extends Model
{
    use HasFactory;

    public const TYPE_BUSINESS_PERMIT = 'business_permit';
    public const TYPE_SAFETY_CERTIFICATE = 'safety_certificate';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'landlord_id',
        'document_type',
        'document_number',
        'file_path',
        'date_issued',
        'expiration_date',
        'verification_status',
        'rejection_reason',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_at',
        'is_current',
        'superseded_at',
    ];

    protected $casts = [
        'date_issued' => 'date',
        'expiration_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_current' => 'boolean',
        'superseded_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Verification scopes ───────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('verification_status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('verification_status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('verification_status', self::STATUS_REJECTED);
    }

    /**
     * Limit a document query to the active version for each document type.
     *
     * Historical versions intentionally remain queryable for audit screens.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeHistory(Builder $query): Builder
    {
        return $query->where('is_current', false);
    }

    // ── Expiration scopes (computed from expiration_date) ─────────

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereDate('expiration_date', '<=', Carbon::today()->toDateString());
    }

    public function scopeExpiringSoon(Builder $query): Builder
    {
        $threshold = (int) config('landlord_documents.expiration.valid_days', 60);

        return $query
            ->whereDate('expiration_date', '>', Carbon::today()->toDateString())
            ->whereDate('expiration_date', '<=', Carbon::today()->addDays($threshold)->toDateString());
    }

    public function scopeValid(Builder $query): Builder
    {
        $threshold = (int) config('landlord_documents.expiration.valid_days', 60);

        return $query->whereDate('expiration_date', '>', Carbon::today()->addDays($threshold)->toDateString());
    }

    // ── Expiration helpers ────────────────────────────────────────

    /**
     * Structured expiration information via the centralized service.
     */
    public function expirationInfo(): array
    {
        return app(DocumentExpirationService::class)->expirationInfo($this->expiration_date);
    }

    /**
     * Supported document types (key => label).
     */
    public static function types(): array
    {
        $types = [];

        foreach ((array) config('landlord_documents.types', []) as $key => $definition) {
            $types[$key] = is_array($definition)
                ? ($definition['label'] ?? ucwords(str_replace('_', ' ', $key)))
                : ucwords(str_replace('_', ' ', $key));
        }

        return $types;
    }

    public static function typeLabel(string $type): string
    {
        $definition = config('landlord_documents.types.' . $type);

        if (is_array($definition)) {
            return $definition['label'] ?? ucwords(str_replace('_', ' ', $type));
        }

        return ucwords(str_replace('_', ' ', $type));
    }

    public static function typeDirectory(string $type): string
    {
        $definition = config('landlord_documents.types.' . $type);

        if (is_array($definition) && filled($definition['directory'] ?? null)) {
            return $definition['directory'];
        }

        return str_replace('_', '-', $type) . 's';
    }

    public static function isSupportedType(string $type): bool
    {
        return array_key_exists($type, self::types());
    }

    public static function verificationStatuses(): array
    {
        return (array) config('landlord_documents.verification_statuses', [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ]);
    }
}
