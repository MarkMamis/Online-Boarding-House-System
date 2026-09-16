<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Services\DocumentExpirationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminLandlordDocumentController extends Controller
{
    public function __construct(protected DocumentExpirationService $expiration)
    {
    }

    // ── Document Verification ─────────────────────────────────────

    public function verification(Request $request)
    {
        $this->syncPendingProfileDocuments();

        $documentType = (string) $request->query('document_type', '');
        $statusFilter = (string) $request->query('verification_status', 'pending');
        $landlordId = (int) $request->query('landlord_id', 0);
        $includeHistory = $request->boolean('history');

        $query = LandlordDocument::query()
            ->with(['landlord:id,full_name,email,contact_number', 'approver:id,full_name'])
            ->when(!$includeHistory, fn ($q) => $q->current())
            ->when($statusFilter !== 'all', fn ($q) => $q->where('verification_status', $statusFilter))
            ->when($documentType !== '' && LandlordDocument::isSupportedType($documentType), fn ($q) => $q->where('document_type', $documentType))
            ->when($landlordId > 0, fn ($q) => $q->where('landlord_id', $landlordId))
            ->orderByDesc('is_current')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        $documents = $query->paginate(15)->withQueryString();

        return view('admin.documents.verification', compact(
            'documents',
            'documentType',
            'statusFilter',
            'landlordId',
            'includeHistory'
        ));
    }

    public function approve(LandlordDocument $document)
    {
        if (!$document->is_current) {
            abort(404);
        }

        $document->update([
            'verification_status' => LandlordDocument::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        $landlord = $document->landlord;
        if ($landlord && $landlord->landlordProfile) {
            $profile = $landlord->landlordProfile;
            if ($document->document_type === LandlordDocument::TYPE_BUSINESS_PERMIT) {
                $profile->update([
                    'business_permit_status' => 'approved',
                    'business_permit_reviewed_at' => Carbon::now(),
                    'business_permit_reviewed_by' => Auth::id(),
                    'business_permit_rejection_reason' => null,
                ]);
            } elseif ($document->document_type === LandlordDocument::TYPE_SAFETY_CERTIFICATE) {
                $profile->update([
                    'safety_certificate_status' => 'approved',
                    'safety_certificate_reviewed_at' => Carbon::now(),
                    'safety_certificate_reviewed_by' => Auth::id(),
                    'safety_certificate_rejection_reason' => null,
                ]);
            }
        }

        return back()->with('success', ucfirst($document->typeLabel($document->document_type)) . ' approved.');
    }

    public function reject(Request $request, LandlordDocument $document)
    {
        if (!$document->is_current) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ], [
            'rejection_reason.required' => 'A rejection reason is required.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rejectionReason = $validator->validated()['rejection_reason'];

        $document->update([
            'verification_status' => LandlordDocument::STATUS_REJECTED,
            'rejection_reason' => $rejectionReason,
            'rejected_at' => Carbon::now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $landlord = $document->landlord;
        if ($landlord && $landlord->landlordProfile) {
            $profile = $landlord->landlordProfile;
            if ($document->document_type === LandlordDocument::TYPE_BUSINESS_PERMIT) {
                $profile->update([
                    'business_permit_status' => 'rejected',
                    'business_permit_reviewed_at' => Carbon::now(),
                    'business_permit_reviewed_by' => Auth::id(),
                    'business_permit_rejection_reason' => $rejectionReason,
                ]);
            } elseif ($document->document_type === LandlordDocument::TYPE_SAFETY_CERTIFICATE) {
                $profile->update([
                    'safety_certificate_status' => 'rejected',
                    'safety_certificate_reviewed_at' => Carbon::now(),
                    'safety_certificate_reviewed_by' => Auth::id(),
                    'safety_certificate_rejection_reason' => $rejectionReason,
                ]);
            }
        }

        return back()->with('success', ucfirst($document->typeLabel($document->document_type)) . ' rejected.');
    }

    // ── Document Monitoring ───────────────────────────────────────

    public function monitoring(Request $request)
    {
        $documentType = (string) $request->query('document_type', '');
        $statusFilter = (string) $request->query('verification_status', 'all');
        $expirationFilter = (string) $request->query('expiration_status', 'all');
        $search = trim((string) $request->query('search', ''));
        $includeHistory = $request->boolean('history');

        $base = LandlordDocument::query()
            ->with(['landlord:id,full_name,email', 'approver:id,full_name'])
            ->when(!$includeHistory, fn ($q) => $q->current())
            ->when($documentType !== '' && LandlordDocument::isSupportedType($documentType), fn ($q) => $q->where('document_type', $documentType))
            ->when($statusFilter !== 'all', fn ($q) => $q->where('verification_status', $statusFilter))
            ->when($expirationFilter === 'valid', fn ($q) => $q->valid())
            ->when($expirationFilter === 'expiring_soon', fn ($q) => $q->expiringSoon())
            ->when($expirationFilter === 'expired', fn ($q) => $q->expired())
            ->when($search !== '', fn ($q) => $q->whereHas('landlord', fn ($l) => $l->where('full_name', 'like', '%' . $search . '%')));

        $documents = $base->orderByDesc('expiration_date')->paginate(15)->withQueryString();

        // Summary statistics from actual queries (expiration is derived from expiration_date).
        $statsBase = LandlordDocument::query()->when(!$includeHistory, fn ($q) => $q->current());
        $stats = [
            'valid' => $this->countExpiration($statsBase, 'valid'),
            'expiring_soon' => $this->countExpiration($statsBase, 'expiring_soon'),
            'expired' => $this->countExpiration($statsBase, 'expired'),
            'pending' => (clone $statsBase)->where('verification_status', LandlordDocument::STATUS_PENDING)->count(),
            'approved' => (clone $statsBase)->where('verification_status', LandlordDocument::STATUS_APPROVED)->count(),
            'rejected' => (clone $statsBase)->where('verification_status', LandlordDocument::STATUS_REJECTED)->count(),
        ];

        return view('admin.documents.monitoring', compact(
            'documents',
            'documentType',
            'statusFilter',
            'expirationFilter',
            'search',
            'includeHistory',
            'stats'
        ));
    }

    protected function countExpiration($query, string $state): int
    {
        return match ($state) {
            'valid' => (clone $query)->valid()->count(),
            'expiring_soon' => (clone $query)->expiringSoon()->count(),
            'expired' => (clone $query)->expired()->count(),
            default => 0,
        };
    }

    /**
     * Self-healing sync: ensure any uploaded legacy or setup landlord profile documents
     * are synchronized to landlord_documents with proper pending status.
     */
    protected function syncPendingProfileDocuments(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('landlord_profiles') || !\Illuminate\Support\Facades\Schema::hasTable('landlord_documents')) {
            return;
        }

        try {
            $profiles = \App\Models\LandlordProfile::query()
                ->where(function ($q) {
                    $q->whereNotNull('business_permit_path')->where('business_permit_path', '!=', '')
                      ->orWhereNotNull('safety_certificate_path')->where('safety_certificate_path', '!=', '');
                })
                ->get();

            foreach ($profiles as $profile) {
                if (!$profile->user_id) {
                    continue;
                }

                $docs = [
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

                foreach ($docs as $type => $info) {
                    $path = trim((string) $info['path']);
                    if ($path === '') {
                        continue;
                    }

                    $rawStatus = (string) $info['status'];
                    $effectiveStatus = in_array($rawStatus, [LandlordDocument::STATUS_APPROVED, LandlordDocument::STATUS_REJECTED], true)
                        ? $rawStatus
                        : LandlordDocument::STATUS_PENDING;

                    $existing = LandlordDocument::where('landlord_id', $profile->user_id)
                        ->where('document_type', $type)
                        ->where('is_current', true)
                        ->first();

                    if (!$existing) {
                        LandlordDocument::create([
                            'landlord_id' => $profile->user_id,
                            'document_type' => $type,
                            'file_path' => $path,
                            'verification_status' => $effectiveStatus,
                            'rejection_reason' => $info['rejection_reason'],
                            'submitted_at' => $profile->created_at ?? now(),
                            'approved_by' => $effectiveStatus === LandlordDocument::STATUS_APPROVED ? $info['reviewed_by'] : null,
                            'approved_at' => $effectiveStatus === LandlordDocument::STATUS_APPROVED ? ($info['reviewed_at'] ?? now()) : null,
                            'rejected_at' => $effectiveStatus === LandlordDocument::STATUS_REJECTED ? ($info['reviewed_at'] ?? now()) : null,
                            'is_current' => true,
                        ]);
                    } elseif ($existing->verification_status === 'not_submitted' || (empty($existing->verification_status) && $effectiveStatus === LandlordDocument::STATUS_PENDING)) {
                        $existing->update(['verification_status' => $effectiveStatus]);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Document verification profile sync error: ' . $e->getMessage());
        }
    }
}
