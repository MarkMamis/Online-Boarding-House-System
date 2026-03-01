@extends('layouts.landlord')

@section('title', 'Tenant Feedback')

@section('content')
<div class="container py-4">
    <div class="glass-card p-4 shadow-sm mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Tenant Feedback</h4>
                <div class="text-muted small">Reviews submitted by tenants for your rooms</div>
            </div>
            <form method="POST" action="{{ route('landlord.feedback.analyze') }}">
                @csrf
                <button type="submit" class="btn btn-outline-brand btn-sm rounded-pill">
                    <i class="bi bi-cpu me-1"></i> Run sentiment analysis
                </button>
            </form>
        </div>

        @if (session('status'))
            <div class="alert alert-info small mt-3 mb-0">{{ session('status') }}</div>
        @endif

        <div class="row g-3 mt-2">
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid rgba(2,8,20,.08);">
                    <div class="text-muted small">Total Feedback</div>
                    <div class="fw-bold" style="font-size:1.4rem;">{{ number_format($totalFeedback) }}</div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-3" style="background:#f8fafc;border:1px solid rgba(2,8,20,.08);">
                    <div class="text-muted small">Average Rating</div>
                    <div class="fw-bold" style="font-size:1.4rem;">
                        {{ $avgRating ? number_format($avgRating, 1) : '—' }}
                        <span class="ms-2" style="font-size:.9rem;color:#f59e0b;">
                            @for($s = 1; $s <= 5; $s++)
                                <i class="bi {{ $avgRating && $s <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($feedbacks->isEmpty())
        <div class="glass-card p-4 text-center text-muted">
            <i class="bi bi-chat-left-text fs-3 d-block mb-2"></i>
            No feedback yet. Once tenants leave reviews, they will appear here.
        </div>
    @else
        <div class="d-grid gap-3">
            @foreach($feedbacks as $feedback)
                <div class="glass-card p-4">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="fw-semibold">{{ $feedback->public_name }}</div>
                            <div class="text-muted small">
                                {{ $feedback->room->property->name ?? 'Property' }} — Room {{ $feedback->room->room_number ?? '' }}
                            </div>
                        </div>
                        <div class="text-muted small">{{ $feedback->created_at->format('M j, Y') }}</div>
                    </div>

                    <div class="mt-2 d-flex align-items-center gap-2 flex-wrap" style="color:#f59e0b;">
                        @for($s = 1; $s <= 5; $s++)
                            <i class="bi {{ $s <= $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                        @endfor
                        <span class="ms-2 text-muted" style="font-size:.85rem;">{{ number_format($feedback->rating, 1) }}</span>
                        @if(!empty($feedback->sentiment_label))
                            @php
                                $sentimentClass = match($feedback->sentiment_label) {
                                    'positive' => 'text-bg-success',
                                    'negative' => 'text-bg-danger',
                                    'neutral' => 'text-bg-secondary',
                                    default => 'text-bg-light',
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $sentimentClass }}" style="font-size:.72rem;">
                                {{ ucfirst($feedback->sentiment_label) }}
                                @if(!is_null($feedback->sentiment_score))
                                    · {{ number_format((float) $feedback->sentiment_score * 100, 0) }}%
                                @endif
                            </span>
                        @endif
                    </div>

                    <p class="mt-2 mb-0" style="color:rgba(2,8,20,.78);">
                        {{ $feedback->comment }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $feedbacks->links() }}
        </div>
    @endif
</div>
@endsection
