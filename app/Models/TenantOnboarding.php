<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantOnboarding extends Model
{
    protected $fillable = [
        'booking_id',
        'status',
        'required_documents',
        'uploaded_documents',
        'contract_content',
        'contract_signed',
        'contract_signed_at',
        'contract_signature_path',
        'contract_signature_name',
        'deposit_amount',
        'advance_amount',
        'payment_method',
        'payment_reference',
        'payment_proof_path',
        'payment_notes',
        'payment_submitted_at',
        'deposit_paid',
        'deposit_paid_at',
        'qr_code_path',
        'digital_id',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'uploaded_documents' => 'array',
        'contract_signed' => 'boolean',
        'contract_signed_at' => 'datetime',
        'deposit_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'payment_submitted_at' => 'datetime',
        'deposit_paid' => 'boolean',
        'deposit_paid_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant()
    {
        return $this->booking->student ?? null;
    }

    public function landlord()
    {
        return $this->booking->room->property->landlord ?? null;
    }

    public function property()
    {
        return $this->booking->room->property ?? null;
    }

    public function room()
    {
        return $this->booking->room ?? null;
    }
}
