<?php

namespace App\Http\Controllers;

use App\Models\LandlordDocument;
use App\Models\Property;
use App\Models\Report;
use App\Models\User;
use App\Services\DocumentExpirationService;
use App\Services\LandlordDocumentStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AdminActionCenterController extends Controller
{
    public function __construct(
        protected LandlordDocumentStatusService $documentStatusService,
        protected DocumentExpirationService $expirationService
    ) {
    }

    public function index(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // 1. Pending Property Reviews
        $pendingPropertiesCount = Property::where('approval_status', 'pending')->count();
        $recentPendingProperties = Property::where('approval_status', 'pending')
            ->with('landlord')
            ->latest()
            ->limit(5)
            ->get();

        // 2. Pending Student Verifications
        $pendingStudentsCount = 0;
        $recentPendingStudents = collect();
        if (Schema::hasColumn('users', 'school_id_verification_status')) {
            $pendingStudentsQuery = User::where('role', 'student')
                ->where(function ($q) {
                    $q->whereNotNull('school_id_path')
                      ->orWhereNotNull('enrollment_proof_path');
                })
                ->where(function ($q) {
                    $q->where('school_id_verification_status', 'pending')
                      ->orWhereNull('school_id_verification_status')
                      ->orWhere('school_id_verification_status', '');
                });

            $pendingStudentsCount = $pendingStudentsQuery->count();
            $recentPendingStudents = $pendingStudentsQuery->latest('updated_at')->limit(5)->get();
        }

        // 3. Pending Landlord Documents
        $pendingDocumentsCount = 0;
        $recentPendingDocuments = collect();
        if (Schema::hasTable('landlord_documents')) {
            $pendingDocumentsQuery = LandlordDocument::current()
                ->where('verification_status', LandlordDocument::STATUS_PENDING)
                ->with('landlord');

            $pendingDocumentsCount = $pendingDocumentsQuery->count();
            $recentPendingDocuments = $pendingDocumentsQuery->latest('submitted_at')->limit(5)->get();
        }

        // 4. Open Urgent Incident Reports
        $urgentReportsCount = Report::where('status', '!=', 'resolved')
            ->where('priority', 'high')
            ->count();
        $recentUrgentReports = Report::where('status', '!=', 'resolved')
            ->with('user')
            ->orderByRaw("CASE WHEN priority = 'high' THEN 1 WHEN priority = 'medium' THEN 2 ELSE 3 END")
            ->latest()
            ->limit(5)
            ->get();

        // 5. Expiring Compliance Documents (approved active documents within warning threshold)
        $expiringComplianceCount = 0;
        if (Schema::hasTable('landlord_documents')) {
            $expiringComplianceCount = LandlordDocument::current()
                ->where('verification_status', LandlordDocument::STATUS_APPROVED)
                ->expiringSoon()
                ->count();
        }

        $totalActionItems = $pendingPropertiesCount + $pendingStudentsCount + $pendingDocumentsCount + $urgentReportsCount;

        return view('admin.action_center.index', compact(
            'pendingPropertiesCount',
            'recentPendingProperties',
            'pendingStudentsCount',
            'recentPendingStudents',
            'pendingDocumentsCount',
            'recentPendingDocuments',
            'urgentReportsCount',
            'recentUrgentReports',
            'expiringComplianceCount',
            'totalActionItems'
        ));
    }
}
