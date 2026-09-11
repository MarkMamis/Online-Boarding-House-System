<?php

namespace App\Services;

use App\Models\LandlordProfile;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class LandlordDocumentStatusService
{
    public const DOC_BUSINESS_PERMIT = 'business_permit';
    public const DOC_SAFETY_CERTIFICATE = 'safety_certificate';

    public const STATUS_MISSING = 'missing';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Check if safety certificate approval columns exist in the database.
     */
    public function supportsSafetyCertificateApproval(): bool
    {
        return Schema::hasColumn('landlord_profiles', 'safety_certificate_status')
            && Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_at')
            && Schema::hasColumn('landlord_profiles', 'safety_certificate_reviewed_by');
    }

    /**
     * Resolve the status of a specific document for a landlord profile.
     * Matches the exact logic from Admin > Landlord Approval > Permit Approval.
     */
    public function resolveDocumentStatus(?LandlordProfile $profile, string $documentType): string
    {
        if (!$profile) {
            return self::STATUS_MISSING;
        }

        $type = strtolower(trim($documentType));
        if ($type === self::DOC_BUSINESS_PERMIT || $type === 'business' || $type === 'permit') {
            if (!filled($profile->business_permit_path)) {
                return self::STATUS_MISSING;
            }

            $rawStatus = (string) ($profile->business_permit_status ?: self::STATUS_PENDING);
            return in_array($rawStatus, [self::STATUS_APPROVED, self::STATUS_REJECTED], true)
                ? $rawStatus
                : self::STATUS_PENDING;
        }

        if ($type === self::DOC_SAFETY_CERTIFICATE || $type === 'safety' || $type === 'certificate') {
            if (!filled($profile->safety_certificate_path)) {
                return self::STATUS_MISSING;
            }

            if ($this->supportsSafetyCertificateApproval()) {
                $rawStatus = (string) ($profile->safety_certificate_status ?: self::STATUS_PENDING);
                return in_array($rawStatus, [self::STATUS_APPROVED, self::STATUS_REJECTED], true)
                    ? $rawStatus
                    : self::STATUS_PENDING;
            }

            return self::STATUS_PENDING;
        }

        return self::STATUS_MISSING;
    }

    /**
     * Get document summary and compliance metrics for an individual landlord.
     */
    public function getLandlordDocumentSummary(User $landlord): array
    {
        $landlord->loadMissing('landlordProfile');
        $profile = $landlord->landlordProfile;

        $bpStatus = $this->resolveDocumentStatus($profile, self::DOC_BUSINESS_PERMIT);
        $scStatus = $this->resolveDocumentStatus($profile, self::DOC_SAFETY_CERTIFICATE);

        $missing = [];
        $pending = [];
        $approved = [];
        $rejected = [];

        if ($bpStatus === self::STATUS_MISSING) {
            $missing[] = 'Business Permit';
        } elseif ($bpStatus === self::STATUS_PENDING) {
            $pending[] = 'Business Permit';
        } elseif ($bpStatus === self::STATUS_APPROVED) {
            $approved[] = 'Business Permit';
        } elseif ($bpStatus === self::STATUS_REJECTED) {
            $rejected[] = 'Business Permit';
        }

        if ($scStatus === self::STATUS_MISSING) {
            $missing[] = 'Safety Certificate';
        } elseif ($scStatus === self::STATUS_PENDING) {
            $pending[] = 'Safety Certificate';
        } elseif ($scStatus === self::STATUS_APPROVED) {
            $approved[] = 'Safety Certificate';
        } elseif ($scStatus === self::STATUS_REJECTED) {
            $rejected[] = 'Safety Certificate';
        }

        $isFullyCompliant = ($bpStatus === self::STATUS_APPROVED && $scStatus === self::STATUS_APPROVED);

        return [
            'landlord_id' => $landlord->id,
            'name' => $landlord->full_name,
            'email' => $landlord->email,
            'contact_number' => $landlord->contact_number,
            'business_permit_status' => $bpStatus,
            'safety_certificate_status' => $scStatus,
            'missing_documents' => $missing,
            'pending_documents' => $pending,
            'approved_documents' => $approved,
            'rejected_documents' => $rejected,
            'is_fully_compliant' => $isFullyCompliant,
            'has_missing_documents' => count($missing) > 0,
            'has_pending_documents' => count($pending) > 0,
            'business_permit_rejection_reason' => $profile?->business_permit_rejection_reason,
            'safety_certificate_rejection_reason' => $profile?->safety_certificate_rejection_reason,
        ];
    }

    /**
     * Compute comprehensive aggregate compliance-document statistics across all landlords.
     */
    public function getAggregateStatistics(): array
    {
        $allLandlords = User::query()
            ->where('role', 'landlord')
            ->with('landlordProfile')
            ->get();

        $totalLandlords = $allLandlords->count();

        $bpCounts = [
            self::STATUS_MISSING => 0,
            self::STATUS_PENDING => 0,
            self::STATUS_APPROVED => 0,
            self::STATUS_REJECTED => 0,
        ];

        $scCounts = [
            self::STATUS_MISSING => 0,
            self::STATUS_PENDING => 0,
            self::STATUS_APPROVED => 0,
            self::STATUS_REJECTED => 0,
        ];

        $landlordsWithMissing = 0;
        $landlordsWithPending = 0;
        $landlordsWithRejected = 0;
        $fullyComplete = 0;

        foreach ($allLandlords as $landlord) {
            $profile = $landlord->landlordProfile;
            $bpStatus = $this->resolveDocumentStatus($profile, self::DOC_BUSINESS_PERMIT);
            $scStatus = $this->resolveDocumentStatus($profile, self::DOC_SAFETY_CERTIFICATE);

            $bpCounts[$bpStatus]++;
            $scCounts[$scStatus]++;

            $hasMissing = ($bpStatus === self::STATUS_MISSING || $scStatus === self::STATUS_MISSING);
            $hasPending = ($bpStatus === self::STATUS_PENDING || $scStatus === self::STATUS_PENDING);
            $hasRejected = ($bpStatus === self::STATUS_REJECTED || $scStatus === self::STATUS_REJECTED);
            $isComplete = ($bpStatus === self::STATUS_APPROVED && $scStatus === self::STATUS_APPROVED);

            if ($hasMissing) {
                $landlordsWithMissing++;
            }
            if ($hasPending) {
                $landlordsWithPending++;
            }
            if ($hasRejected) {
                $landlordsWithRejected++;
            }
            if ($isComplete) {
                $fullyComplete++;
            }
        }

        $totalMissingInstances = $bpCounts[self::STATUS_MISSING] + $scCounts[self::STATUS_MISSING];

        return [
            'total_landlords' => $totalLandlords,
            'business_permit' => $bpCounts,
            'safety_certificate' => $scCounts,
            'landlords_with_missing_documents' => $landlordsWithMissing,
            'landlords_with_pending_documents' => $landlordsWithPending,
            'landlords_with_rejected_documents' => $landlordsWithRejected,
            'total_missing_document_instances' => $totalMissingInstances,
            'fully_document_complete_landlords' => $fullyComplete,
        ];
    }

    /**
     * Get list of landlords with missing documents.
     */
    public function getLandlordsWithMissingDocuments(?string $documentType = null, int $limit = 20): array
    {
        $allLandlords = User::query()
            ->where('role', 'landlord')
            ->with('landlordProfile')
            ->orderBy('full_name')
            ->get();

        $type = strtolower(trim((string) $documentType));
        $list = [];

        foreach ($allLandlords as $landlord) {
            $summary = $this->getLandlordDocumentSummary($landlord);

            if ($type === self::DOC_BUSINESS_PERMIT || $type === 'business') {
                if ($summary['business_permit_status'] === self::STATUS_MISSING) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'missing_documents' => ['Business Permit'],
                    ];
                }
            } elseif ($type === self::DOC_SAFETY_CERTIFICATE || $type === 'safety') {
                if ($summary['safety_certificate_status'] === self::STATUS_MISSING) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'missing_documents' => ['Safety Certificate'],
                    ];
                }
            } else {
                if ($summary['has_missing_documents']) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'missing_documents' => $summary['missing_documents'],
                        'business_permit_status' => $summary['business_permit_status'],
                        'safety_certificate_status' => $summary['safety_certificate_status'],
                    ];
                }
            }
        }

        return [
            'total_affected_landlords' => count($list),
            'landlords' => array_slice($list, 0, $limit),
        ];
    }

    /**
     * Get list of landlords with submitted documents pending administrator review.
     */
    public function getLandlordsWithPendingDocuments(?string $documentType = null, int $limit = 20): array
    {
        $allLandlords = User::query()
            ->where('role', 'landlord')
            ->with('landlordProfile')
            ->orderBy('full_name')
            ->get();

        $type = strtolower(trim((string) $documentType));
        $list = [];

        foreach ($allLandlords as $landlord) {
            $summary = $this->getLandlordDocumentSummary($landlord);

            if ($type === self::DOC_BUSINESS_PERMIT || $type === 'business') {
                if ($summary['business_permit_status'] === self::STATUS_PENDING) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'pending_documents' => ['Business Permit'],
                    ];
                }
            } elseif ($type === self::DOC_SAFETY_CERTIFICATE || $type === 'safety') {
                if ($summary['safety_certificate_status'] === self::STATUS_PENDING) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'pending_documents' => ['Safety Certificate'],
                    ];
                }
            } else {
                if ($summary['has_pending_documents']) {
                    $list[] = [
                        'landlord_id' => $landlord->id,
                        'name' => $landlord->full_name,
                        'email' => $landlord->email,
                        'pending_documents' => $summary['pending_documents'],
                    ];
                }
            }
        }

        return [
            'total_pending_landlords' => count($list),
            'landlords' => array_slice($list, 0, $limit),
        ];
    }
}
