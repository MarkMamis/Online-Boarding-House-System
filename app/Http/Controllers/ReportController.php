<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    // Student: Show create report form
    public function create()
    {
        $this->authorize('create', Report::class);
        return view('student.reports.create');
    }

    // Student: Store new report
    public function store(Request $request)
    {
        $this->authorize('create', Report::class);
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        if ($validator->fails()) {
            return redirect()->to(route('student.dashboard') . '#reports')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        Report::create([
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
        ]);

        return redirect()->to(route('student.dashboard') . '#reports')->with('success', 'Report submitted successfully!');
    }

    // Admin: List all reports
    public function index()
    {
        $this->authorize('update', new Report());
        $reports = Report::with('user')->latest()->paginate(10);
        return view('admin.reports.index', compact('reports'));
    }

    // Admin: Show single report
    public function show(Report $report)
    {
        $this->authorize('update', $report);
        return view('admin.reports.show', compact('report'));
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
    public function studentIndex()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $reports = $user->reports()->latest()->paginate(10);
        return view('student.reports.index', compact('reports'));
    }

    // Student: Mark admin response as read
    public function markResponseRead(Report $report)
    {
        $this->authorize('markResponseRead', $report);

        $report->update(['response_read' => true]);

        return response()->json(['success' => true]);
    }
}

