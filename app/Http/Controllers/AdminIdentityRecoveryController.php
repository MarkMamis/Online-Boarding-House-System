<?php

namespace App\Http\Controllers;

use App\Models\IdentityRecoveryRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AdminIdentityRecoveryController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (! Schema::hasTable('identity_recovery_requests')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Identity recovery requests table is not available yet.');
        }

        $statusFilter = strtolower((string) $request->query('status', 'pending'));
        if (! in_array($statusFilter, ['pending', 'approved', 'rejected', 'all'], true)) {
            $statusFilter = 'pending';
        }

        $query = IdentityRecoveryRequest::with(['user', 'reviewer'])->latest();

        $counts = [
            'pending' => IdentityRecoveryRequest::where('status', 'pending')->count(),
            'approved' => IdentityRecoveryRequest::where('status', 'approved')->count(),
            'rejected' => IdentityRecoveryRequest::where('status', 'rejected')->count(),
            'total' => IdentityRecoveryRequest::count(),
        ];

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $requests = $query->paginate(15)->withQueryString();

        // For each request, find the existing account holding that student_id to show verification context
        $requests->getCollection()->transform(function ($req) {
            $req->existing_account = User::where('student_id', $req->student_id)
                ->where('id', '!=', $req->user_id)
                ->first(['id', 'full_name', 'email', 'role', 'school_id_verification_status', 'created_at']);

            return $req;
        });

        return view('admin.approvals.identity_recoveries', compact('requests', 'statusFilter', 'counts'));
    }

    public function approve(Request $request, IdentityRecoveryRequest $recovery)
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $recovery->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        if ($recovery->user) {
            $recovery->user->notify(new SystemNotification(
                'Identity recovery approved',
                'Your account recovery request for Student ID '.$recovery->student_id.' has been approved by an administrator. Please check your contact email for further instructions.',
                route('student.setup.show'),
                ['type' => 'identity_recovery_approved'],
                sendEmail: true,
                actionText: 'Access Student Account'
            ));
        }

        return back()->with('success', 'Identity recovery request approved successfully.');
    }

    public function reject(Request $request, IdentityRecoveryRequest $recovery)
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $recovery->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        if ($recovery->user) {
            $recovery->user->notify(new SystemNotification(
                'Identity recovery rejected',
                'Your account recovery request for Student ID '.$recovery->student_id.' was rejected. Reason: '.$validated['admin_notes'],
                route('student.setup.show'),
                [
                    'type' => 'identity_recovery_rejected',
                    'reason' => $validated['admin_notes'],
                ],
                sendEmail: true,
                actionText: 'Review Recovery Status'
            ));
        }

        return back()->with('success', 'Identity recovery request rejected.');
    }
}
