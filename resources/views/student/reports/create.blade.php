@extends('layouts.student_dashboard')

@section('title', 'Submit Report')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold mb-0">Submit a Report</h4>
        <a href="{{ route('student.reports.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Back</a>
    </div>

    <div class="border rounded-4 bg-white shadow-sm p-4">
        <div class="small text-muted mb-3">Use this to report issues/concerns. An admin will respond.</div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('student.reports.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <label class="form-label small text-muted">Title</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g., Broken lock / Noise complaint" required>
                </div>
                <div class="col-12 col-lg-4">
                    <label class="form-label small text-muted">Priority</label>
                    <select name="priority" class="form-select" required>
                        <option value="low" @selected(old('priority', 'medium')==='low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium')==='medium')>Medium</option>
                        <option value="high" @selected(old('priority', 'medium')==='high')>High</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Describe the issue clearly…" required>{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-brand rounded-pill px-4">Submit report</button>
            </div>
        </form>
    </div>
</div>
@endsection
