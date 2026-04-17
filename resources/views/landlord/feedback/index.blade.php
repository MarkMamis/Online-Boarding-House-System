@extends('layouts.landlord')

@section('title', 'Tenant Feedback')

@section('content')
@php
    $feedbackCollection = $feedbacks instanceof \Illuminate\Contracts\Pagination\Paginator
        ? collect($feedbacks->items())
        : collect($feedbacks);

    $positiveCount = $feedbackCollection->where('sentiment_label', 'positive')->count();
    $neutralCount = $feedbackCollection->where('sentiment_label', 'neutral')->count();
    $negativeCount = $feedbackCollection->where('sentiment_label', 'negative')->count();
    $positiveRate = $feedbackCollection->count() > 0
        ? (int) round(($positiveCount / $feedbackCollection->count()) * 100)
        : 0;
@endphp

<div class="glass-card rounded-4 p-4 p-md-5 feedback-shell">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase small text-muted fw-semibold">Review Hub</div>
            <h1 class="h3 mb-1">Tenant Feedback</h1>
            <p class="text-muted mb-0">Track ratings, sentiment, and tenant comments across your room listings.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('landlord.rooms.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-door-open me-1"></i> View Rooms
            </a>
            <!-- <a href="{{ route('landlord.dashboard') }}" class="btn btn-brand rounded-pill px-3">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
            </a> -->
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-info small rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="feedback-metric h-100">
                <div class="feedback-metric-label">Total Feedback</div>
                <div class="feedback-metric-value">{{ number_format($totalFeedback) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feedback-metric h-100">
                <div class="feedback-metric-label">Average Rating</div>
                <div class="feedback-metric-value">
                    {{ $avgRating ? number_format($avgRating, 1) : '0.0' }}
                </div>
                <div class="small mt-1 rating-stars">
                    @for($s = 1; $s <= 5; $s++)
                        <i class="bi {{ $avgRating && $s <= round($avgRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feedback-metric h-100">
                <div class="feedback-metric-label">Positive Sentiment</div>
                <div class="feedback-metric-value text-success">{{ $positiveRate }}%</div>
                <div class="small text-muted mt-1">{{ number_format($positiveCount) }} of {{ number_format(max($feedbackCollection->count(), 1)) }} on this page</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="feedback-metric h-100">
                <div class="feedback-metric-label">Sentiment Mix</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <span class="badge rounded-pill text-bg-success">{{ $positiveCount }} Positive</span>
                    <span class="badge rounded-pill text-bg-secondary">{{ $neutralCount }} Neutral</span>
                    <span class="badge rounded-pill text-bg-danger">{{ $negativeCount }} Negative</span>
                </div>
            </div>
        </div>
    </div>

    @if($feedbackCollection->isEmpty())
        <div class="text-center rounded-4 border-2 border-dashed py-5 px-3 bg-white text-muted">
            <i class="bi bi-chat-left-text fs-2 d-block mb-2"></i>
            <div class="h5 mb-1 text-dark">No feedback yet</div>
            <p class="mb-0">Once tenants leave reviews, they will appear here.</p>
        </div>
    @else
        <div class="vstack gap-3">
            @foreach($feedbacks as $feedback)
                @php
                    $sentimentLabel = strtolower((string) ($feedback->sentiment_label ?? ''));
                    $sentimentClass = match($sentimentLabel) {
                        'positive' => 'text-bg-success',
                        'negative' => 'text-bg-danger',
                        'neutral' => 'text-bg-secondary',
                        default => 'text-bg-light',
                    };

                    $displayName = trim((string) ($feedback->public_name ?? 'Tenant'));
                    $initial = strtoupper(substr($displayName, 0, 1));
                @endphp

                <article class="feedback-card rounded-4 p-3 p-md-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div class="d-flex align-items-start gap-3 min-w-0">
                            <div class="feedback-avatar">{{ $initial }}</div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $displayName }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $feedback->room->property->name ?? 'Property' }} - {{ $feedback->room->room_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">{{ $feedback->created_at->format('M j, Y') }}</div>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                        <span class="rating-stars">
                            @for($s = 1; $s <= 5; $s++)
                                <i class="bi {{ $s <= (int) $feedback->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </span>
                        <span class="small text-muted">{{ number_format((float) $feedback->rating, 1) }} / 5.0</span>

                        @if($sentimentLabel !== '')
                            <span class="badge rounded-pill {{ $sentimentClass }}">
                                {{ ucfirst($sentimentLabel) }}
                                @if(!is_null($feedback->sentiment_score))
                                    · {{ number_format((float) $feedback->sentiment_score * 100, 0) }}%
                                @endif
                            </span>
                        @endif
                    </div>

                    <p class="feedback-comment mb-0 mt-3">{{ $feedback->comment }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-4 feedback-pagination">
            {{ $feedbacks->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .feedback-shell {
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
    }
    .feedback-metric {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        padding: .95rem 1rem;
        box-shadow: 0 8px 20px rgba(2,8,20,.04);
    }
    .feedback-metric-label {
        font-size: .78rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: rgba(2,8,20,.55);
    }
    .feedback-metric-value {
        font-size: 1.45rem;
        font-weight: 700;
        color: #166534;
        line-height: 1.2;
        margin-top: .2rem;
    }
    .feedback-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        box-shadow: 0 8px 20px rgba(2,8,20,.05);
    }
    .feedback-avatar {
        width: 42px;
        height: 42px;
        border-radius: .85rem;
        background: rgba(22,101,52,.12);
        border: 1px solid rgba(22,101,52,.24);
        color: #166534;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex: 0 0 auto;
    }
    .rating-stars {
        color: #f59e0b;
        letter-spacing: .06em;
    }
    .feedback-comment {
        color: rgba(2,8,20,.78);
        line-height: 1.6;
        white-space: pre-line;
    }
    .feedback-pagination nav {
        display: flex;
        justify-content: center;
    }
</style>
@endpush

