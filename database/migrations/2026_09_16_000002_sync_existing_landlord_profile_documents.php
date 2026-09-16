<?php

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('landlord_profiles') || !Schema::hasTable('landlord_documents')) {
            return;
        }

        LandlordProfile::query()
            ->where(function ($q) {
                $q->whereNotNull('business_permit_path')->where('business_permit_path', '!=', '')
                    ->orWhereNotNull('safety_certificate_path')->where('safety_certificate_path', '!=', '');
            })
            ->with('user:id,role')
            ->orderBy('id')
            ->chunkById(100, function ($profiles) {
                foreach ($profiles as $profile) {
                    if (!$profile->user || $profile->user->role !== 'landlord') {
                        continue;
                    }

                    $map = [
                        LandlordDocument::TYPE_BUSINESS_PERMIT => [
                            'path' => $profile->business_permit_path,
                            'status' => $profile->business_permit_status,
                            'rejection_reason' => $profile->business_permit_rejection_reason ?? null,
                            'reviewed_by' => $profile->business_permit_reviewed_by ?? null,
                            'reviewed_at' => $profile->business_permit_reviewed_at ?? null,
                        ],
                        LandlordDocument::TYPE_SAFETY_CERTIFICATE => [
                            'path' => $profile->safety_certificate_path,
                            'status' => $profile->safety_certificate_status,
                            'rejection_reason' => $profile->safety_certificate_rejection_reason ?? null,
                            'reviewed_by' => $profile->safety_certificate_reviewed_by ?? null,
                            'reviewed_at' => $profile->safety_certificate_reviewed_at ?? null,
                        ],
                    ];

                    foreach ($map as $type => $legacy) {
                        $path = trim((string) $legacy['path']);
                        if ($path === '') {
                            continue;
                        }

                        $status = (string) ($legacy['status'] ?: LandlordDocument::STATUS_PENDING);
                        if (!in_array($status, [LandlordDocument::STATUS_PENDING, LandlordDocument::STATUS_APPROVED, LandlordDocument::STATUS_REJECTED], true)) {
                            $status = LandlordDocument::STATUS_PENDING;
                        }

                        $document = LandlordDocument::where('landlord_id', $profile->user_id)
                            ->where('document_type', $type)
                            ->where('is_current', true)
                            ->first();

                        if ($document) {
                            // Keep status synchronized if drifted
                            $updates = [];
                            if ($document->verification_status !== $status) {
                                $updates['verification_status'] = $status;
                            }
                            if (empty($document->file_path) && filled($path)) {
                                $updates['file_path'] = $path;
                            }
                            if (!empty($updates)) {
                                $document->update($updates);
                            }
                            continue;
                        }

                        LandlordDocument::create([
                            'landlord_id' => $profile->user_id,
                            'document_type' => $type,
                            'file_path' => $path,
                            'verification_status' => $status,
                            'rejection_reason' => filled($legacy['rejection_reason']) ? $legacy['rejection_reason'] : null,
                            'submitted_at' => $profile->created_at ?? now(),
                            'approved_by' => ($status === LandlordDocument::STATUS_APPROVED && filled($legacy['reviewed_by'])) ? $legacy['reviewed_by'] : null,
                            'approved_at' => ($status === LandlordDocument::STATUS_APPROVED) ? ($legacy['reviewed_at'] ?? now()) : null,
                            'rejected_at' => ($status === LandlordDocument::STATUS_REJECTED) ? ($legacy['reviewed_at'] ?? now()) : null,
                            'is_current' => true,
                        ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe migration: does not drop data.
    }
};
