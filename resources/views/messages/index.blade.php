@extends('layouts.student_dashboard')

@section('title', 'Messages')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Messages</h4>
    </div>

    @php
        $dashboardErrors = $errors->getBag('messages_dashboard');
        $messageErrors = $dashboardErrors->any() ? $dashboardErrors : $errors;
        $threads = $messageThreads ?? collect();
        $activeThreadKey = old('_thread_key');
    @endphp

    @if($messageErrors->any())
        <div class="alert alert-danger rounded-4 mb-3">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach($messageErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3" style="min-height:420px;">
        <div class="col-12 col-lg-4">
            <div class="border rounded-4 bg-white shadow-sm overflow-hidden h-100" style="min-height:420px;">
                <div class="px-3 py-2 border-bottom bg-light" style="font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(2,8,20,.45);">Conversations</div>
                @if($threads->isEmpty())
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-chat-square-text fs-3 d-block mb-2" style="color:rgba(2,8,20,.2);"></i>
                        No conversations yet.<br>
                        Message a landlord from a room page.
                    </div>
                @else
                    <div class="list-group list-group-flush rounded-0" id="threadList">
                        @foreach($threads as $tidx => $t)
                        @php
                            $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : $tidx === 0;
                            $latest = $t['latest'];
                            $isMine = (int)$latest->sender_id === Auth::id();
                            $preview = \Illuminate\Support\Str::limit($latest->body, 60);
                        @endphp
                        <button type="button"
                                class="list-group-item list-group-item-action px-3 py-2 text-start thread-btn {{ $isActive ? 'active' : '' }}"
                                data-thread="{{ $tidx }}">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:36px;height:36px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.2);">
                                    <i class="bi bi-person" style="color:var(--brand);font-size:.85rem;"></i>
                                </div>
                                <div class="flex-fill min-w-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-truncate" style="font-size:.86rem;max-width:120px;">{{ $t['other']->full_name ?? 'Landlord' }}</span>
                                        @if($t['unread'] > 0)
                                            <span class="badge rounded-pill text-bg-danger ms-1" style="font-size:.65rem;">{{ $t['unread'] }}</span>
                                        @endif
                                    </div>
                                    @if($t['property'])
                                        <div class="text-muted text-truncate" style="font-size:.72rem;">{{ $t['property']->name }}</div>
                                    @endif
                                    <div class="text-muted text-truncate" style="font-size:.73rem;">
                                        {{ $isMine ? 'You: ' : '' }}{{ $preview }}
                                    </div>
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="border rounded-4 bg-white shadow-sm d-flex flex-column h-100" style="min-height:420px;">
                <div class="flex-fill position-relative" style="overflow:hidden;">
                    @if($threads->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted">
                            <i class="bi bi-chat-dots fs-1 mb-2" style="color:rgba(2,8,20,.15);"></i>
                            <div class="small">Select a conversation or start a new inquiry from a room page.</div>
                        </div>
                    @else
                        @foreach($threads as $tidx => $t)
                        @php $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : $tidx === 0; @endphp
                        <div class="thread-view d-flex flex-column {{ $isActive ? '' : 'd-none' }}" data-thread-view="{{ $tidx }}" style="height:100%;">
                            <div class="px-3 py-2 border-bottom d-flex align-items-center gap-2" style="background:#fafafa;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:32px;height:32px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.18);">
                                    <i class="bi bi-person" style="color:var(--brand);font-size:.8rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.88rem;">{{ $t['other']->full_name ?? 'Landlord' }}</div>
                                    @if($t['property'])
                                    <div class="text-muted" style="font-size:.72rem;"><i class="bi bi-building me-1"></i>{{ $t['property']->name }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-fill overflow-auto p-3 d-flex flex-column gap-2 thread-msg-list" style="max-height:280px;">
                                @foreach(array_reverse($t['messages']) as $msg)
                                @php $mine = (int)$msg->sender_id === Auth::id(); @endphp
                                <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div style="max-width:78%;">
                                        <div class="px-3 py-2 rounded-3" style="font-size:.85rem;line-height:1.5;{{ $mine ? 'background:var(--brand);color:#fff;border-radius:1rem 1rem 0 1rem!important;' : 'background:#f1f5f9;color:#0f172a;border-radius:1rem 1rem 1rem 0!important;' }}">{{ $msg->body }}</div>
                                        <div class="mt-1" style="font-size:.67rem;color:rgba(2,8,20,.4);text-align:{{ $mine ? 'right' : 'left' }};">
                                            {{ $mine ? 'You' : ($t['other']->full_name ?? 'Landlord') }} · {{ $msg->created_at->diffForHumans() }}
                                            @if($mine && $msg->read_at)<i class="bi bi-check2-all ms-1" style="color:var(--brand);"></i>@endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="border-top p-3" style="background:#fafafa;">
                                <form method="POST" action="{{ route('messages.store') }}" class="d-flex gap-2 align-items-end">
                                    @csrf
                                    <input type="hidden" name="_thread_key" value="{{ $tidx }}">
                                    <input type="hidden" name="receiver_id" value="{{ $t['other']->id ?? '' }}">
                                    <input type="hidden" name="property_id" value="{{ $t['property_id'] ?? '' }}">
                                    <textarea name="body" rows="2" required
                                              class="form-control rounded-3 flex-fill"
                                              placeholder="Type a reply…"
                                              style="resize:none;font-size:.86rem;border-color:rgba(2,8,20,.12);"></textarea>
                                    <button type="submit" class="btn btn-brand rounded-pill px-3 flex-shrink-0" style="font-size:.85rem;">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 rounded-4 p-3 d-flex align-items-center gap-2" style="background:rgba(22,101,52,.05);border:1px solid rgba(22,101,52,.15);">
        <i class="bi bi-info-circle" style="color:var(--brand);font-size:1rem;"></i>
        <span class="small text-muted">To start a new conversation, open a room from <a href="{{ route('student.rooms.index') }}" class="text-decoration-none fw-semibold" style="color:var(--brand);">Browse Rooms</a> and use the inquiry form.</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        const threadBtn = e.target.closest('.thread-btn');
        if (!threadBtn) return;
        const threadId = threadBtn.dataset.thread;
        document.querySelectorAll('.thread-btn').forEach(b => b.classList.remove('active'));
        threadBtn.classList.add('active');
        document.querySelectorAll('.thread-view').forEach(v => {
            v.classList.toggle('d-none', v.dataset.threadView !== threadId);
        });
        const msgList = document.querySelector(`.thread-view[data-thread-view="${threadId}"] .thread-msg-list`);
        if (msgList) msgList.scrollTop = msgList.scrollHeight;
    });
    document.querySelectorAll('.thread-msg-list').forEach(el => { el.scrollTop = el.scrollHeight; });
</script>
@endpush
