<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportPriorityClassifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function __construct(private readonly ReportPriorityClassifier $reportPriorityClassifier)
    {
    }

    private function canSubmitStudentReport(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user || $user->role !== 'student') {
            return false;
        }

        return $user->bookings()
            ->where('status', 'approved')
            ->whereDate('check_out', '>', now()->toDateString())
            ->whereHas('tenantOnboarding', function ($query) {
                $query->where('status', 'completed');
            })
            ->exists();
    }

    // Student: Show create report form
    public function create()
    {
        if (!$this->canSubmitStudentReport()) {
            return redirect()->route('student.reports.index')
                ->with('error', 'Only verified tenants can submit reports.');
        }

        return redirect()->route('student.reports.index', ['compose' => 1]);
    }

    // Student: Store new report
    public function store(Request $request)
    {
        if (!$this->canSubmitStudentReport()) {
            if ($request->boolean('from_dashboard')) {
                return redirect()->to(route('student.dashboard') . '#reports')
                    ->with('error', 'Only verified tenants can submit reports.');
            }

            return redirect()->route('student.reports.index')
                ->with('error', 'Only verified tenants can submit reports.');
        }

        $this->authorize('create', Report::class);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            if ($request->boolean('from_dashboard')) {
                return redirect()->to(route('student.dashboard') . '#reports')
                    ->withErrors($validator)
                    ->withInput();
            }

            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        $aiPriority = $this->reportPriorityClassifier->classify(
            (string) $data['title'],
            (string) $data['description']
        );

        Report::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $aiPriority,
        ]);

        if ($request->boolean('from_dashboard')) {
            return redirect()->to(route('student.dashboard') . '#reports')->with('success', 'Report submitted successfully!');
        }

        return redirect()->route('student.reports.index')->with('success', 'Report submitted successfully!');
    }

    // Admin: List all reports
    public function index(Request $request)
    {
        $this->authorize('update', new Report());

        $statusFilter = strtolower((string) $request->query('status', 'all'));
        $priorityFilter = strtolower((string) $request->query('priority', 'all'));
        $search = trim((string) $request->query('search', ''));

        $query = Report::with(['user.bookings.room.property'])->latest();

        if (in_array($statusFilter, ['pending', 'in_progress', 'resolved'], true)) {
            $query->where('status', $statusFilter);
        }

        if (in_array($priorityFilter, ['high', 'medium', 'low'], true)) {
            $query->where('priority', $priorityFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->paginate(10)->withQueryString();

        $counts = [
            'all' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'in_progress' => Report::where('status', 'in_progress')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'high_priority' => Report::where('priority', 'high')->where('status', '!=', 'resolved')->count(),
        ];

        return view('admin.reports.index', compact('reports', 'statusFilter', 'priorityFilter', 'search', 'counts'));
    }

    // Admin: Show single report
    public function show(Report $report)
    {
        $this->authorize('update', $report);
        $report->loadMissing(['user.bookings.room.property']);
        $activeBooking = $report->user?->bookings()
            ->where('status', 'approved')
            ->where('check_out', '>', now()->toDateString())
            ->with(['room.property.landlord'])
            ->first();

        return view('admin.reports.show', compact('report', 'activeBooking'));
    }

    // Admin: Update report status and response
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_response' => 'nullable|string',
        ]);

        $data = [
            'status' => $request->status,
            'admin_response' => $request->admin_response,
        ];

        if ($request->status === 'resolved') {
            $data['resolved_at'] = now();
        }

        // If admin is adding/updating a response, mark it as unread for the student
        if (!empty($request->admin_response)) {
            $data['response_read'] = false;
        }

        $report->update($data);

        return redirect()->route('admin.reports.index')->with('success', 'Report updated successfully!');
    }

    // Student: List their own reports
    public function studentIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reports = $user->reports()->latest()->paginate(10);
        $canSubmitReport = $this->canSubmitStudentReport();
        $openCompose = $request->boolean('compose');

        return view('student.reports.index', compact('reports', 'canSubmitReport', 'openCompose'));
    }

    // Student: Mark admin response as read
    public function markResponseRead(Report $report)
    {
        $this->authorize('markResponseRead', $report);

        $report->update(['response_read' => true]);

        return response()->json(['success' => true]);
    }
}

