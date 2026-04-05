@extends('layouts.landlord')

@section('content')
<div class="glass-card rounded-4 p-4 p-md-5 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-semibold mb-0">Messages</h4>
        <small class="text-muted">Manage tenant communications</small>
    </div>

    @php
        $threads = collect($messages ?? collect())
            ->sortByDesc('created_at')
            ->groupBy(function ($m) use ($user) {
                $otherId = ((int) $m->sender_id === (int) $user->id) ? $m->receiver_id : $m->sender_id;
                $propertyId = $m->property_id ?? 0;
                return $otherId . '_' . $propertyId;
            })
            ->map(function ($group) use ($user, $participantCategories) {
                $latest = $group->first();
                $other = ((int) $latest->sender_id === (int) $user->id) ? $latest->receiver : $latest->sender;
                $category = $participantCategories[(int) ($other->id ?? 0)] ?? [
                    'slug' => 'direct',
                    'label' => 'Direct Inquiry',
                ];
                return [
                    'other' => $other,
                    'property' => $latest->property,
                    'property_id' => $latest->property_id,
                    'category' => $category,
                    'latest' => $latest,
                    'unread' => $group->filter(fn ($m) => empty($m->read_at) && (int) $m->receiver_id === (int) $user->id)->count(),
                    'messages' => $group->sortBy('created_at')->values(),
                ];
            })
            ->values();

        $activeThreadKey = old('_thread_key');
        $preferredReceiver = request()->integer('to');
        if ($activeThreadKey === null && $preferredReceiver > 0) {
            $matched = $threads->search(fn ($t) => (int) ($t['other']->id ?? 0) === $preferredReceiver);
            if ($matched !== false) {
                $activeThreadKey = (string) $matched;
            }
        }
    @endphp

    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-3">
            <div class="fw-semibold mb-1">Please fix the following:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $mobileChatOpen = $threads->isNotEmpty() && (($activeThreadKey !== null) || $preferredReceiver > 0);
    @endphp

    <div class="row g-3 messages-layout {{ $mobileChatOpen ? 'messages-mobile-chat' : '' }}" id="messagesLayout" data-mobile-chat-open="{{ $mobileChatOpen ? '1' : '0' }}" style="min-height:480px;">
        <div class="col-12 col-lg-4 messages-inbox-pane">
            <div class="border rounded-4 bg-white shadow-sm overflow-hidden h-100" style="min-height:480px;">
                <div class="px-3 py-2 border-bottom bg-light" style="font-size:.76rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(2,8,20,.45);">Conversations</div>
                <div class="p-2 border-bottom bg-white">
                    <div class="d-flex gap-2 align-items-center">
                        <input type="text" id="threadSearchInput" class="form-control form-control-sm" placeholder="Search conversations...">
                        <button type="button" class="btn btn-outline-brand btn-sm rounded-pill text-nowrap" data-bs-toggle="modal" data-bs-target="#startMessageModal">
                            <i class="bi bi-plus-lg me-1"></i>New Message
                        </button>
                    </div>
                </div>
                @if($threads->isEmpty())
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-chat-square-text fs-3 d-block mb-2" style="color:rgba(2,8,20,.2);"></i>
                        No conversations yet.
                    </div>
                @else
                    <div class="list-group list-group-flush rounded-0" id="threadList">
                        @foreach($threads as $tidx => $t)
                        @php
                            $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : $tidx === 0;
                            $latest = $t['latest'];
                            $isMine = (int)$latest->sender_id === (int)$user->id;
                            $preview = \Illuminate\Support\Str::limit($latest->body, 60);
                            $searchText = strtolower(trim(($t['other']->full_name ?? 'user') . ' ' . ($t['property']->name ?? '') . ' ' . $preview));
                            $categorySlug = (string) ($t['category']['slug'] ?? 'direct');
                            $categoryClass = match ($categorySlug) {
                                'current_tenant' => 'thread-category-current',
                                'former_tenant' => 'thread-category-former',
                                'prospective' => 'thread-category-prospective',
                                default => 'thread-category-direct',
                            };
                            $categoryIcon = match ($categorySlug) {
                                'current_tenant' => 'bi-person-check',
                                'former_tenant' => 'bi-person-dash',
                                'prospective' => 'bi-hourglass-split',
                                default => 'bi-chat-dots',
                            };
                        @endphp
                        <button type="button"
                                class="list-group-item list-group-item-action px-3 py-2 text-start thread-btn {{ $isActive ? 'active' : '' }}"
                                data-thread="{{ $tidx }}"
                                data-search="{{ $searchText }}">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                     style="width:36px;height:36px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.2);">
                                    <i class="bi bi-person" style="color:var(--brand);font-size:.85rem;"></i>
                                </div>
                                <div class="flex-fill min-w-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-truncate" style="font-size:.86rem;max-width:125px;">{{ $t['other']->full_name ?? 'User' }}</span>
                                        @if($t['unread'] > 0)
                                            <span class="badge rounded-pill text-bg-danger ms-1" style="font-size:.65rem;">{{ $t['unread'] }}</span>
                                        @endif
                                    </div>
                                    <div class="thread-meta-row mt-1">
                                        <span class="thread-category {{ $categoryClass }}">
                                            <i class="bi {{ $categoryIcon }}" aria-hidden="true"></i>
                                            <span>{{ $t['category']['label'] ?? 'Direct Inquiry' }}</span>
                                        </span>
                                        @if($t['property'])
                                            <span class="thread-property-chip text-truncate" title="{{ $t['property']->name }}">{{ $t['property']->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size:.73rem;">
                                        {{ $isMine ? 'You: ' : '' }}{{ $preview }}
                                    </div>
                                </div>
                            </div>
                        </button>
                        @endforeach
                    </div>
                    <div id="threadSearchEmpty" class="p-3 text-center text-muted small d-none">No conversations match your search.</div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-8 messages-chat-pane">
            <div class="border rounded-4 bg-white shadow-sm d-flex flex-column h-100" style="min-height:480px;">
                <div class="flex-fill position-relative" style="overflow:hidden;">
                    @if($threads->isEmpty())
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 p-4 text-center text-muted">
                            <i class="bi bi-chat-dots fs-1 mb-2" style="color:rgba(2,8,20,.15);"></i>
                            <div class="small">Select a conversation to reply.</div>
                        </div>
                    @else
                        @foreach($threads as $tidx => $t)
                        @php $isActive = $activeThreadKey !== null ? (string)$activeThreadKey === (string)$tidx : $tidx === 0; @endphp
                        <div class="thread-view d-flex flex-column {{ $isActive ? '' : 'd-none' }}" data-thread-view="{{ $tidx }}" style="height:100%;">
                            <div class="px-3 py-2 border-bottom d-flex align-items-center justify-content-between gap-2" style="background:#fafafa;">
                                @php
                                    $headerCategorySlug = (string) ($t['category']['slug'] ?? 'direct');
                                    $headerCategoryClass = match ($headerCategorySlug) {
                                        'current_tenant' => 'thread-category-current',
                                        'former_tenant' => 'thread-category-former',
                                        'prospective' => 'thread-category-prospective',
                                        default => 'thread-category-direct',
                                    };
                                    $headerCategoryIcon = match ($headerCategorySlug) {
                                        'current_tenant' => 'bi-person-check',
                                        'former_tenant' => 'bi-person-dash',
                                        'prospective' => 'bi-hourglass-split',
                                        default => 'bi-chat-dots',
                                    };
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 mobile-back-btn d-lg-none" aria-label="Back to inbox" title="Back to inbox">
                                        <i class="bi bi-arrow-left-circle fs-5"></i>
                                    </button>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shrink-0"
                                         style="width:32px;height:32px;background:rgba(22,101,52,.10);border:1px solid rgba(22,101,52,.18);">
                                        <i class="bi bi-person" style="color:var(--brand);font-size:.8rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold" style="font-size:.88rem;">{{ $t['other']->full_name ?? 'User' }}</div>
                                        <div>
                                            <span class="thread-category {{ $headerCategoryClass }}">
                                                <i class="bi {{ $headerCategoryIcon }}" aria-hidden="true"></i>
                                                <span>{{ $t['category']['label'] ?? 'Direct Inquiry' }}</span>
                                            </span>
                                        </div>
                                        @if($t['property'])
                                        <div class="text-muted" style="font-size:.72rem;"><i class="bi bi-building me-1"></i>{{ $t['property']->name }}</div>
                                        @endif
                                    </div>
                                </div>
                                @php $latestUnread = collect($t['messages'])->first(fn($m) => empty($m->read_at) && (int)$m->receiver_id === (int)$user->id); @endphp
                                @if($latestUnread)
                                    <form method="POST" action="{{ route('messages.read', $latestUnread->id) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success rounded-pill">Mark Read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="flex-fill overflow-auto p-3 d-flex flex-column gap-2 thread-msg-list" style="max-height:320px;">
                                @foreach($t['messages'] as $msg)
                                @php $mine = (int)$msg->sender_id === (int)$user->id; @endphp
                                <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                                    <div style="max-width:78%;">
                                        <div class="px-3 py-2 rounded-3" style="font-size:.85rem;line-height:1.5;{{ $mine ? 'background:var(--brand);color:#fff;border-radius:1rem 1rem 0 1rem!important;' : 'background:#f1f5f9;color:#0f172a;border-radius:1rem 1rem 1rem 0!important;' }}">{{ $msg->body }}</div>
                                        <div class="mt-1" style="font-size:.67rem;color:rgba(2,8,20,.4);text-align:{{ $mine ? 'right' : 'left' }};">
                                            {{ $mine ? 'You' : ($t['other']->full_name ?? 'User') }} · {{ $msg->created_at->diffForHumans() }}
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
                                              placeholder="Type a reply..."
                                              style="resize:none;font-size:.86rem;border-color:rgba(2,8,20,.12);"></textarea>
                                    <button type="submit" class="btn btn-brand rounded-pill px-3 shrink-0" style="font-size:.85rem;">
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
</div>

<div class="modal fade" id="startMessageModal" tabindex="-1" aria-labelledby="startMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="startMessageModalLabel">Start Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('messages.store') }}" id="startMessageForm">
                @csrf
                <div class="modal-body">
                    @if($recipients->isNotEmpty())
                        <div class="mb-3">
                            <div class="small fw-semibold text-uppercase text-muted mb-2" style="letter-spacing:.05em;">Contact List</div>
                            <div class="list-group" id="recipientList">
                                @foreach($recipients as $r)
                                    <button type="button" class="list-group-item list-group-item-action recipient-option" data-recipient-id="{{ $r->id }}" data-recipient-name="{{ $r->full_name }}">
                                        <div class="fw-semibold">{{ $r->full_name }}</div>
                                        <div class="small text-muted">{{ ucfirst($r->role) }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <input type="hidden" name="receiver_id" id="startMessageReceiverId" required>
                        <div class="mb-2 small text-muted" id="startMessageSelectedLabel">No recipient selected</div>
                        <div>
                            <textarea name="body" rows="3" class="form-control" required placeholder="Type your message..."></textarea>
                        </div>
                    @else
                        <div class="alert alert-light border mb-0">
                            <div class="fw-semibold mb-1">No contacts available</div>
                            <div class="small text-muted">Contacts appear here after you interact with tenants.</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand rounded-pill px-3" @if($recipients->isEmpty()) disabled @endif>
                        <i class="bi bi-send me-1"></i>Send Message
                    </button>
                </div>
            </form>
        </div>
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
    .recipient-option.active {
        background: rgba(167, 243, 208, .52);
        border-color: rgba(22, 101, 52, .28);
        color: #14532d;
    }
    .thread-meta-row {
        display: flex;
        align-items: center;
        gap: .35rem;
        min-width: 0;
        flex-wrap: wrap;
    }
    .thread-category {
        display: inline-flex;
        align-items: center;
        gap: .28rem;
        border-radius: 999px;
        padding: .16rem .52rem;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .02em;
        border: 1px solid;
        line-height: 1.25;
        box-shadow: 0 1px 0 rgba(15, 23, 42, .05);
    }
    .thread-category i {
        font-size: .62rem;
        line-height: 1;
    }
    .thread-property-chip {
        display: inline-flex;
        align-items: center;
        max-width: 148px;
        border-radius: 999px;
        padding: .14rem .5rem;
        font-size: .65rem;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .thread-category-current {
        color: #14532d;
        background: #dcfce7;
        border-color: #86efac;
    }
    .thread-category-prospective {
        color: #9a3412;
        background: #ffedd5;
        border-color: #fdba74;
    }
    .thread-category-former {
        color: #1d4ed8;
        background: #dbeafe;
        border-color: #93c5fd;
    }
    .thread-category-direct {
        color: #374151;
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .thread-btn.active .thread-category {
        filter: saturate(1.05);
    }
    .mobile-back-btn {
        color: #14532d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .mobile-back-btn:hover {
        color: #166534;
    }

    @media (max-width: 991.98px) {
        .messages-layout .messages-chat-pane {
            display: none;
        }
        .messages-layout.messages-mobile-chat .messages-inbox-pane {
            display: none;
        }
        .messages-layout.messages-mobile-chat .messages-chat-pane {
            display: block;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const messagesLayout = document.getElementById('messagesLayout');
    const isMobileMessagesView = () => window.matchMedia('(max-width: 991.98px)').matches;

    const setActiveThread = (threadId, options = {}) => {
        const { openChat = false } = options;

        document.querySelectorAll('.thread-btn').forEach((button) => {
            button.classList.toggle('active', button.dataset.thread === String(threadId));
        });

        document.querySelectorAll('.thread-view').forEach((view) => {
            view.classList.toggle('d-none', view.dataset.threadView !== String(threadId));
        });

        const msgList = document.querySelector(`.thread-view[data-thread-view="${threadId}"] .thread-msg-list`);
        if (msgList) msgList.scrollTop = msgList.scrollHeight;

        if (openChat && isMobileMessagesView() && messagesLayout) {
            messagesLayout.classList.add('messages-mobile-chat');
        }
    };

    document.addEventListener('click', function (e) {
        const threadBtn = e.target.closest('.thread-btn');
        if (threadBtn) {
            setActiveThread(threadBtn.dataset.thread, { openChat: true });
            return;
        }

        const backBtn = e.target.closest('.mobile-back-btn');
        if (backBtn && messagesLayout) {
            messagesLayout.classList.remove('messages-mobile-chat');
        }
    });

    document.querySelectorAll('.thread-msg-list').forEach((list) => {
        list.scrollTop = list.scrollHeight;
    });

    const recipientButtons = document.querySelectorAll('.recipient-option');
    const receiverInput = document.getElementById('startMessageReceiverId');
    const selectedLabel = document.getElementById('startMessageSelectedLabel');
    const threadSearchInput = document.getElementById('threadSearchInput');
    const threadSearchEmpty = document.getElementById('threadSearchEmpty');

    if (threadSearchInput) {
        threadSearchInput.addEventListener('input', function () {
            const query = (this.value || '').trim().toLowerCase();
            const threadButtons = Array.from(document.querySelectorAll('.thread-btn'));
            let visibleCount = 0;

            threadButtons.forEach((button) => {
                const haystack = (button.dataset.search || '').toLowerCase();
                const isVisible = query === '' || haystack.includes(query);
                button.classList.toggle('d-none', !isVisible);
                if (isVisible) visibleCount++;
            });

            if (threadSearchEmpty) {
                threadSearchEmpty.classList.toggle('d-none', visibleCount > 0);
            }

            const activeButton = threadButtons.find((button) => button.classList.contains('active') && !button.classList.contains('d-none'));
            if (!activeButton) {
                const firstVisible = threadButtons.find((button) => !button.classList.contains('d-none'));
                if (firstVisible) {
                    setActiveThread(firstVisible.dataset.thread);
                } else {
                    document.querySelectorAll('.thread-view').forEach((view) => view.classList.add('d-none'));
                }
            }
        });
    }

    recipientButtons.forEach((button) => {
        button.addEventListener('click', function () {
            recipientButtons.forEach((item) => item.classList.remove('active'));
            this.classList.add('active');
            if (receiverInput) {
                receiverInput.value = this.dataset.recipientId || '';
            }
            if (selectedLabel) {
                selectedLabel.textContent = `To: ${this.dataset.recipientName || 'Selected contact'}`;
            }
        });
    });

    if (messagesLayout && isMobileMessagesView()) {
        if (messagesLayout.dataset.mobileChatOpen !== '1') {
            messagesLayout.classList.remove('messages-mobile-chat');
        }
    }
</script>
@endpush