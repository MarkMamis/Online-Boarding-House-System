<?php

namespace App\Console\Commands;

use App\Models\LandlordDocument;
use App\Models\LandlordProfile;
use Illuminate\Console\Command;

class BackfillLandlordDocuments extends Command
{
    protected $signature = 'landlord-documents:backfill {--dry-run : Show what would be created without writing}';

    protected $description = 'Backfill landlord_documents from legacy business_permit_path / safety_certificate_path columns (idempotent)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;

        LandlordProfile::query()
            ->where(function ($q) {
                $q->whereNotNull('business_permit_path')->where('business_permit_path', '!=', '')
                    ->orWhereNotNull('safety_certificate_path')->where('safety_certificate_path', '!=', '');
            })
            ->with('user:id,role')
            ->orderBy('user_id')
            ->chunkById(100, function ($profiles) use (&$created, &$skipped, $dryRun) {
                foreach ($profiles as $profile) {
                    if (!$profile->user || $profile->user->role !== 'landlord') {
                        continue;
                    }

                    $map = [
                        LandlordDocument::TYPE_BUSINESS_PERMIT => [
                            'path' => $profile->business_permit_path,
                            'status' => $profile->business_permit_status,
                            'rejection_reason' => $profile->business_permit_rejection_reason,
                            'reviewed_by' => $profile->business_permit_reviewed_by,
                            'reviewed_at' => $profile->business_permit_reviewed_at,
                        ],
                        LandlordDocument::TYPE_SAFETY_CERTIFICATE => [
                            'path' => $profile->safety_certificate_path,
                            'status' => $profile->safety_certificate_status,
                            'rejection_reason' => $profile->safety_certificate_rejection_reason,
                            'reviewed_by' => $profile->safety_certificate_reviewed_by,
                            'reviewed_at' => $profile->safety_certificate_reviewed_at,
                        ],
                    ];

                    foreach ($map as $type => $legacy) {
                        $path = trim((string) $legacy['path']);

                        if ($path === '') {
                            continue;
                        }

                        $exists = LandlordDocument::where('landlord_id', $profile->user_id)
                            ->where('document_type', $type)
                            ->exists();

                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        $status = (string) ($legacy['status'] ?: LandlordDocument::STATUS_PENDING);
                        if (!in_array($status, [LandlordDocument::STATUS_PENDING, LandlordDocument::STATUS_APPROVED, LandlordDocument::STATUS_REJECTED], true)) {
                            $status = LandlordDocument::STATUS_PENDING;
                        }

                        $attributes = [
                            'landlord_id' => $profile->user_id,
                            'document_type' => $type,
                            'file_path' => $path,
                            'verification_status' => $status,
                            'rejection_reason' => filled($legacy['rejection_reason']) ? $legacy['rejection_reason'] : null,
                            'submitted_at' => $profile->created_at,
                            'approved_by' => $status === LandlordDocument::STATUS_APPROVED && filled($legacy['reviewed_by']) ? $legacy['reviewed_by'] : null,
                            'approved_at' => $status === LandlordDocument::STATUS_APPROVED ? $legacy['reviewed_at'] : null,
                            'rejected_at' => $status === LandlordDocument::STATUS_REJECTED ? $legacy['reviewed_at'] : null,
                            'is_current' => true,
                        ];

                        if ($dryRun) {
                            $this->line(sprintf('  [dry-run] would create %s for landlord #%d (%s)', $type, $profile->user_id, $path));
                        } else {
                            LandlordDocument::create($attributes);
                            $this->line(sprintf('  Created %s for landlord #%d', $type, $profile->user_id));
                        }

                        $created++;
                    }
                }
            });

        $this->newLine();
        $this->info(sprintf(
            '%s: %d record(s) %s, %d skipped (already present).',
            $dryRun ? 'Dry run' : 'Backfill complete',
            $created,
            $dryRun ? 'would be created' : 'created',
            $skipped
        ));

        return self::SUCCESS;
    }
}
