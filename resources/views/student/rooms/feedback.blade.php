@extends('layouts.student_dashboard')

@section('title', 'Room Feedback')

@push('styles')
<style>
    .rf-shell {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: 1rem;
        box-shadow: 0 8px 18px rgba(2,8,20,.06);
        padding: 1rem;
    }
    .rf-head {
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .9rem;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdfc 100%);
        padding: .9rem 1rem;
        margin-bottom: .9rem;
    }
    .rf-stat {
        background: #f8fafc;
        border: 1px solid rgba(2,8,20,.07);
        border-radius: .75rem;
        padding: .7rem .85rem;
    }
    .rf-stat .k {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: rgba(2,8,20,.48);
    }
    .rf-stat .v {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
        margin-top: .2rem;
    }
    .rf-card {
        background: #fff;
        border: 1px solid rgba(2,8,20,.08);
        border-radius: .9rem;
        box-shadow: 0 6px 16px rgba(2,8,20,.05);
    }
    .rf-card-header {
        padding: .85rem 1rem;
        border-bottom: 1px solid rgba(2,8,20,.07);
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .rf-card-body {
        padding: 1rem;
    }
    .rf-feedback-item:last-child {
        border-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    .rf-star-pick {
        transition: color .12s, transform .1s;
    }
    .rf-star-pick:hover {
        transform: scale(1.2);
    }
</style>
@endpush

@section('content')
@php
    $avg = $avgRating ? number_format($avgRating, 1) : null;
    $reviewsCount = $feedbacks->count();
@endphp

<div class="rf-shell">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div class="small">
            <a href="{{ route('student.rooms.show', $room->id) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Room
            </a>
        </div>
        @if($tenantMode)
            <span class="badge rounded-pill text-bg-success">My Room</span>
        @endif
    </div>

    <div class="rf-head">
        <div class="fw-bold">{{ $room->room_number }} Feedback</div>
        <div class="text-muted small">{{ $room->property->name }} • {{ $room->property->address }}</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="rf-stat">
                <div class="k">Average Rating</div>
                <div class="v">
                    @if($avg)
                        {{ $avg }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="rf-stat">
                <div class="k">Total Reviews</div>
                <div class="v">{{ $reviewsCount }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="rf-stat">
                <div class="k">Your Feedback</div>
                <div class="v">{{ $alreadyFeedback ? 'Submitted' : ($canFeedback ? 'Eligible' : 'Not Eligible') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <div class="rf-card h-100">
                <div class="rf-card-header"><i class="bi bi-chat-left-text"></i>Tenant Reviews</div>
                <div class="rf-card-body">
                    @if(($feedbacks->count() ?? 0) > 0)
                        <div class="mb-3" style="border:1px solid rgba(2,8,20,.07);border-radius:.75rem;padding:.75rem;background:#f8fafc;">
                            <div class="small fw-semibold mb-2" style="color:#0f172a;">Rating breakdown</div>
                            <div class="d-flex flex-column gap-1">
                                @foreach(($feedbackDistribution ?? collect()) as $bucket)
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:42px;font-size:.75rem;color:#334155;">{{ $bucket['stars'] }} <i class="bi bi-star-fill" style="color:#f59e0b;"></i></div>
                                        <div style="height:8px;flex:1;background:rgba(148,163,184,.24);border-radius:999px;overflow:hidden;">
                                            <div style="height:100%;width:{{ $bucket['percent'] }}%;background:#f59e0b;"></div>
                                        </div>
                                        <div style="width:34px;text-align:right;font-size:.74rem;color:#64748b;">{{ $bucket['count'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-3" id="feedbackList">
                            @foreach($feedbacks as $fb)
                                <div class="rf-feedback-item pb-3" style="border-bottom:1px solid rgba(2,8,20,.06);">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                             style="width:34px;height:34px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.2);">
                                            <i class="bi bi-person" style="color:var(--brand);font-size:.85rem;"></i>
                                        </div>
                                        <div class="flex-fill min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="fw-semibold" style="font-size:.88rem;">{{ $fb->public_name }}</span>
                                                <div class="d-flex gap-1">
                                                    @for($s=1;$s<=5;$s++)
                                                        <i class="bi {{ $s <= $fb->rating ? 'bi-star-fill' : 'bi-star' }}" style="color:#f59e0b;font-size:.72rem;"></i>
                                                    @endfor
                                                </div>
                                                <span class="text-muted ms-auto" style="font-size:.72rem;">{{ $fb->created_at->format('M j, Y') }}</span>
                                            </div>
                                            <p class="mb-0 mt-1" style="font-size:.85rem;color:rgba(2,8,20,.75);line-height:1.5;">{{ $fb->comment !== '' ? $fb->comment : 'No written comment. Star rating only.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-3 p-3 text-center" style="background:#f8fafc;border:1px solid rgba(2,8,20,.06);">
                            <i class="bi bi-star fs-4 d-block mb-1" style="color:rgba(2,8,20,.2);"></i>
                            <span class="text-muted small">No reviews yet for this room.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="rf-card h-100">
                <div class="rf-card-header"><i class="bi bi-pencil-square"></i>Leave Feedback</div>
                <div class="rf-card-body">
                    @if($canFeedback && !$alreadyFeedback)
                        <form method="POST" action="{{ route('student.rooms.feedback', $room->id) }}" id="feedbackForm">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" style="font-size:.8rem;font-weight:600;color:rgba(2,8,20,.55);text-transform:uppercase;letter-spacing:.05em;">Rating</label>
                                <div class="d-flex gap-2" id="starPicker">
                                    @for($s=1;$s<=5;$s++)
                                        <i class="bi bi-star rf-star-pick" data-val="{{ $s }}" style="font-size:1.5rem;color:#d1d5db;cursor:pointer;"></i>
                                    @endfor
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating') }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" style="font-size:.8rem;font-weight:600;color:rgba(2,8,20,.55);text-transform:uppercase;letter-spacing:.05em;">Comment (Optional)</label>
                                <textarea name="comment"
                                          class="form-control rounded-3"
                                          rows="3"
                                          placeholder="Share additional details about your stay (optional)..."
                                          style="resize:none;font-size:.88rem;border-color:rgba(2,8,20,.12);">{{ old('comment') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" style="font-size:.8rem;font-weight:600;color:rgba(2,8,20,.55);text-transform:uppercase;letter-spacing:.05em;">Display Name</label>
                                <input type="text" name="display_name" id="displayNameInput"
                                       class="form-control rounded-3"
                                       placeholder="Your name (leave blank to use your account name)"
                                       value="{{ old('display_name') }}"
                                       style="font-size:.88rem;border-color:rgba(2,8,20,.12);">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="anonymous" value="1" id="anonCheck" {{ old('anonymous') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="anonCheck" style="font-size:.82rem;">Post anonymously</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-brand w-100 rounded-pill fw-semibold" style="font-size:.88rem;">
                                <i class="bi bi-send me-1"></i> Submit Feedback
                            </button>
                        </form>
                    @elseif($alreadyFeedback)
                        <div class="rounded-3 p-3" style="background:rgba(22,101,52,.06);border:1px solid rgba(22,101,52,.18);">
                            <i class="bi bi-check-circle-fill me-2" style="color:var(--brand);"></i>
                            <span style="font-size:.86rem;font-weight:600;color:var(--brand);">You've already submitted feedback for this room.</span>
                        </div>
                    @else
                        <div class="rounded-3 p-3" style="background:#f8fafc;border:1px solid rgba(2,8,20,.07);">
                            <i class="bi bi-info-circle me-2 text-muted"></i>
                            <span class="text-muted" style="font-size:.84rem;">Only verified tenants can rate or leave feedback for this room.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const stars = document.querySelectorAll('.rf-star-pick');
    const rInput = document.getElementById('ratingInput');
    const anonChk = document.getElementById('anonCheck');
    const nameInp = document.getElementById('displayNameInput');

    function paintStars(val) {
        stars.forEach((s) => {
            const v = parseInt(s.dataset.val, 10);
            s.classList.toggle('bi-star-fill', v <= val);
            s.classList.toggle('bi-star', v > val);
            s.style.color = v <= val ? '#f59e0b' : '#d1d5db';
        });
    }

    stars.forEach((s) => {
        s.addEventListener('mouseover', () => paintStars(parseInt(s.dataset.val, 10)));
        s.addEventListener('mouseleave', () => paintStars(parseInt((rInput && rInput.value) || 0, 10)));
        s.addEventListener('click', () => {
            if (rInput) {
                rInput.value = s.dataset.val;
                paintStars(parseInt(s.dataset.val, 10));
            }
        });
    });

    if (rInput && rInput.value) {
        paintStars(parseInt(rInput.value, 10));
    }

    if (anonChk && nameInp) {
        const toggleName = () => {
            const wrap = nameInp.closest('.mb-3');
            if (wrap) {
                wrap.style.opacity = anonChk.checked ? '.45' : '1';
            }
            nameInp.disabled = anonChk.checked;
        };
        anonChk.addEventListener('change', toggleName);
        toggleName();
    }
</script>
@endpush

