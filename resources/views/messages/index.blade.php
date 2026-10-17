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

    <div class="row g-3" id="chatMainContainer" style="min-height:480px;">
        {{-- Thread List Column --}}
        <div class="col-12 col-lg-4 chat-sidebar-col" id="chatSidebarCol">
            <div class="border rounded-4 bg-white shadow-sm overflow-hidden h-100" style="min-height:420px;">
                <div class="px-3 py-2.5 border-bottom bg-light d-flex align-items-center justify-content-between">
                    <span style="font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(2,8,20,.5);">Conversations</span>
                    <span class="badge bg-white text-secondary border rounded-pill">{{ $threads->count() }}</span>
                </div>
                @if($threads->isEmpty())
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-chat-square-text fs-3 d-block mb-2 opacity-50"></i>
                        No conversations yet.<br>
                        Message a landlord from any room detail page.
                    </div>
                @else
                    <div class="list-group list-group-flush rounded-0" id="threadList">
                        @foreach($threads as $tidx => $t)
                        @php
                            $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : false;
                            $latest = $t['latest'];
                            $isMine = (int)$latest->sender_id === Auth::id();
                            $preview = \Illuminate\Support\Str::limit($latest->body, 50);
                        @endphp
                        <button type="button"
                                class="list-group-item list-group-item-action px-3 py-2.5 text-start thread-btn {{ $isActive ? 'active' : '' }}"
                                data-thread="{{ $tidx }}">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                     style="width:38px;height:38px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.2);">
                                    <i class="bi bi-person-fill" style="color:var(--brand);font-size:1rem;"></i>
                                </div>
                                <div class="flex-fill min-w-0">
                                    <div class="d-flex justify-content-between align-items-center mb-0.5">
                                        <span class="fw-bold text-truncate text-dark" style="font-size:.88rem;max-width:130px;">{{ $t['other']->full_name ?? 'Landlord' }}</span>
                                        @if($t['unread'] > 0)
                                            <span class="badge rounded-pill text-bg-danger ms-1" style="font-size:.65rem;">{{ $t['unread'] }}</span>
                                        @endif
                                    </div>
                                    @if($t['property'])
                                        <div class="text-muted text-truncate" style="font-size:.72rem;"><i class="bi bi-building me-1"></i>{{ $t['property']->name }}</div>
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

        {{-- Thread Detail / Chat Column --}}
        <div class="col-12 col-lg-8 chat-detail-col d-none d-lg-block" id="chatDetailCol">
            <div class="border rounded-4 bg-white shadow-sm d-flex flex-column h-100" style="min-height:480px;">
                <div class="flex-fill position-relative d-flex flex-column" style="overflow:hidden;">
                    @if($threads->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted">
                            <i class="bi bi-chat-dots fs-1 mb-2 opacity-50"></i>
                            <div class="small">Select a conversation or start a new inquiry from a room page.</div>
                        </div>
                    @else
                        {{-- No Thread Selected Placeholder on Desktop --}}
                        <div id="noThreadSelected" class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted {{ $activeThreadKey !== null ? 'd-none' : '' }}">
                            <i class="bi bi-chat-left-dots fs-1 mb-2 text-secondary opacity-50"></i>
                            <div class="fw-semibold text-dark mb-1">Select a Conversation</div>
                            <div class="small text-muted">Choose a landlord conversation on the left to view messages.</div>
                        </div>

                        @foreach($threads as $tidx => $t)
                        @php $isActive = $activeThreadKey !== null && (string)$activeThreadKey === (string)$tidx; @endphp
                        <div class="thread-view flex-column {{ $isActive ? 'd-flex' : 'd-none' }} h-100" data-thread-view="{{ $tidx }}">
                            <div class="px-3 py-2.5 border-bottom d-flex align-items-center justify-content-between bg-light">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-lg-none btn-back-to-threads p-0 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" aria-label="Back to conversations">
                                        <i class="bi bi-arrow-left"></i>
                                    </button>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                         style="width:34px;height:34px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.18);">
                                        <i class="bi bi-person-fill" style="color:var(--brand);font-size:.9rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size:.9rem;">{{ $t['other']->full_name ?? 'Landlord' }}</div>
                                        @if($t['property'])
                                        <div class="text-muted" style="font-size:.73rem;"><i class="bi bi-building me-1"></i>{{ $t['property']->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex-fill overflow-auto p-3 d-flex flex-column gap-2 thread-msg-list" style="max-height:360px;">
                                @foreach(array_reverse($t['messages']) as $msg)
                                @php $mine = (int)$msg->sender_id === Auth::id(); @endphp
                                <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div style="max-width:82%;">
                                        <div class="px-3 py-2 rounded-4 shadow-sm" style="font-size:.86rem;line-height:1.5;{{ $mine ? 'background:#14532d;color:#fff;border-bottom-right-radius:.25rem!important;' : 'background:#f1f5f9;color:#0f172a;border-bottom-left-radius:.25rem!important;' }}">{{ $msg->body }}</div>
                                        <div class="mt-1" style="font-size:.67rem;color:rgba(2,8,20,.45);text-align:{{ $mine ? 'right' : 'left' }};">
                                            {{ $mine ? 'You' : ($t['other']->full_name ?? 'Landlord') }} · {{ $msg->created_at->diffForHumans() }}
                                            @if($mine && $msg->read_at)<i class="bi bi-check2-all ms-1 text-success"></i>@endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="border-top p-3 bg-light mt-auto">
                                <form method="POST" action="{{ route('messages.store') }}" class="d-flex gap-2 align-items-end">
                                    @csrf
                                    <input type="hidden" name="_thread_key" value="{{ $tidx }}">
                                    <input type="hidden" name="receiver_id" value="{{ $t['other']->id ?? '' }}">
                                    <input type="hidden" name="property_id" value="{{ $t['property_id'] ?? '' }}">
                                    <textarea name="body" rows="2" required
                                              class="form-control rounded-3 flex-fill"
                                              placeholder="Type your reply to {{ $t['other']->full_name ?? 'landlord' }}…"
                                              style="resize:none;font-size:.88rem;border-color:rgba(2,8,20,.12);"></textarea>
                                    <button type="submit" class="btn btn-success rounded-pill px-3.5 py-2 shrink-0 fw-semibold">
                                        <i class="bi bi-send-fill me-1"></i> Send
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
        <i class="bi bi-info-circle-fill text-success fs-5"></i>
        <span class="small text-muted">To start a new conversation with a landlord, open any room from <a href="{{ route('student.rooms.index') }}" class="text-success text-decoration-none fw-semibold">Explore Rooms</a> and send an inquiry.</span>
    </div>
</div>
@endsection

@push('styles')
<style>
    .thread-btn.active,
    .thread-btn.active:hover,
    .thread-btn.active:focus {
        background: rgba(167, 243, 208, .52) !important;
        border-color: rgba(22, 101, 52, .28) !important;
        color: #14532d !important;
    }
    .thread-btn.active .text-muted {
        color: rgba(20, 83, 45, .72) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarCol = document.getElementById('chatSidebarCol');
        const detailCol = document.getElementById('chatDetailCol');
        const noThreadSelected = document.getElementById('noThreadSelected');

        const openThread = (threadId) => {
            document.querySelectorAll('.thread-btn').forEach(b => b.classList.remove('active'));
            const activeBtn = document.querySelector(`.thread-btn[data-thread="${threadId}"]`);
            if (activeBtn) activeBtn.classList.add('active');

            if (noThreadSelected) noThreadSelected.classList.add('d-none');

            document.querySelectorAll('.thread-view').forEach(v => {
                const match = v.dataset.threadView === String(threadId);
                v.classList.toggle('d-flex', match);
                v.classList.toggle('d-none', !match);
            });

            // On mobile viewports: hide sidebar, show detail
            if (window.innerWidth < 992) {
                if (sidebarCol) sidebarCol.classList.add('d-none');
                if (detailCol) detailCol.classList.remove('d-none');
            }

            const msgList = document.querySelector(`.thread-view[data-thread-view="${threadId}"] .thread-msg-list`);
            if (msgList) msgList.scrollTop = msgList.scrollHeight;
        };

        const backToThreads = () => {
            if (sidebarCol) sidebarCol.classList.remove('d-none');
            if (detailCol) detailCol.classList.add('d-none');
            document.querySelectorAll('.thread-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.thread-view').forEach(v => {
                v.classList.remove('d-flex');
                v.classList.add('d-none');
            });
            if (noThreadSelected) noThreadSelected.classList.remove('d-none');
        };

        document.addEventListener('click', function(e) {
            const threadBtn = e.target.closest('.thread-btn');
            if (threadBtn) {
                openThread(threadBtn.dataset.thread);
                return;
            }

            const backBtn = e.target.closest('.btn-back-to-threads');
            if (backBtn) {
                backToThreads();
                return;
            }
        });

        // If there's an active thread from validation or URL, open it
        const activeBtn = document.querySelector('.thread-btn.active');
        if (activeBtn) {
            openThread(activeBtn.dataset.thread);
        } else if (window.innerWidth >= 992) {
            const firstBtn = document.querySelector('.thread-btn');
            if (firstBtn) openThread(firstBtn.dataset.thread);
        }

        document.querySelectorAll('.thread-msg-list').forEach(el => { el.scrollTop = el.scrollHeight; });
    });
</script>
@endpush
