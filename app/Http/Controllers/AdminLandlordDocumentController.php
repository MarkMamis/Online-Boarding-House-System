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

        $document->update([
            'verification_status' => LandlordDocument::STATUS_REJECTED,
            'rejection_reason' => $validator->validated()['rejection_reason'],
            'rejected_at' => Carbon::now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

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
}
