@php
    $chatbotRole = Auth::user()->role ?? 'guest';
@endphp

<div id="chatbotWidget" data-role="{{ $chatbotRole }}" class="chatbot-widget">
    <button type="button" class="chatbot-toggle" id="chatbotToggle" aria-label="Open AI Assistant">
        <i class="bi bi-chat-dots-fill"></i>
    </button>

    <div class="chatbot-panel" id="chatbotPanel" aria-hidden="true">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="d-flex align-items-center gap-2">
                <div class="chatbot-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <div class="chatbot-title d-flex align-items-center gap-1">
                        <span>OBHS Assistant</span>
                        <span class="chatbot-status-badge" title="Online"></span>
                    </div>
                    <div class="chatbot-subtitle">{{ ucfirst($chatbotRole) }} Portal Assistant</div>
                </div>
            </div>
            <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Close chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Messages list -->
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-welcome">
                <div class="chatbot-welcome-icon">
                    <i class="bi bi-stars"></i>
                </div>
                <h6>Kumusta! How can I help you today?</h6>
                <p>Maaari kang magtanong tungkol sa rooms, bookings, payments, reports, o system statistics.</p>
            </div>
        </div>

        <!-- Input area -->
        <div class="chatbot-input">
            <form id="chatbotForm">
                @csrf
                <div class="chatbot-input-wrap">
                    <input
                        type="text"
                        id="chatbotText"
                        placeholder="Magtanong tungkol sa system..."
                        maxlength="2000"
                        autocomplete="off"
                        required
                    />
                    <button type="submit" id="chatbotSubmit" class="chatbot-send-btn" aria-label="Send message">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .chatbot-widget {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 9999;
        font-family: inherit;
    }

    .chatbot-toggle {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #15803d, #166534);
        color: #ffffff;
        box-shadow: 0 10px 25px rgba(22, 101, 52, 0.35);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .chatbot-toggle:hover {
        transform: scale(1.05);
        box-shadow: 0 14px 30px rgba(22, 101, 52, 0.45);
    }
    .chatbot-toggle:active {
        transform: scale(0.96);
    }

    .chatbot-panel {
        position: absolute;
        right: 0;
        bottom: 68px;
        width: min(420px, calc(100vw - 1.5rem));
        height: min(560px, calc(100vh - 85px));
        max-height: 600px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.12);
        border-radius: 1.25rem;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: chatbotFadeIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .chatbot-panel.open {
        display: flex;
    }

    @media (max-width: 480px) {
        .chatbot-widget {
            right: 0.75rem;
            bottom: 0.75rem;
        }
        .chatbot-panel {
            right: -0.25rem;
            bottom: 62px;
            width: calc(100vw - 1rem);
            height: calc(100vh - 80px);
            max-height: 560px;
        }
    }

    @keyframes chatbotFadeIn {
        from {
            opacity: 0;
            transform: translateY(12px) scale(0.97);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Header */
    .chatbot-header {
        padding: 0.85rem 1.1rem;
        background: rgba(248, 250, 252, 0.95);
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chatbot-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #dcfce7;
        color: #15803d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .chatbot-title {
        font-weight: 700;
        font-size: 0.92rem;
        color: #0f172a;
        line-height: 1.2;
    }
    .chatbot-status-badge {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
        display: inline-block;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.25);
    }
    .chatbot-subtitle {
        font-size: 0.73rem;
        color: #64748b;
        font-weight: 500;
    }
    .chatbot-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .chatbot-close:hover {
        background: rgba(15, 23, 42, 0.06);
        color: #334155;
    }

    /* Messages List */
    .chatbot-messages {
        flex: 1;
        padding: 1rem;
        overflow-y: auto;
        overscroll-behavior: contain;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        scroll-behavior: smooth;
        background: #fafbfc;
    }

    /* Welcome block */
    .chatbot-welcome {
        text-align: center;
        padding: 1.25rem 0.75rem;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .chatbot-welcome-icon {
        font-size: 1.75rem;
        color: #16a34a;
        margin-bottom: 0.5rem;
    }
    .chatbot-welcome h6 {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
        margin-bottom: 0.35rem;
    }
    .chatbot-welcome p {
        font-size: 0.8rem;
        margin: 0;
        line-height: 1.4;
    }

    /* Chat Bubbles */
    .chatbot-bubble {
        max-width: 88%;
        padding: 0.75rem 0.95rem;
        font-size: 0.87rem;
        line-height: 1.55;
        overflow-wrap: break-word;
        word-break: break-word;
        border-radius: 1.1rem;
    }

    .chatbot-bubble.user {
        background: #dcfce7;
        color: #14532d;
        border: 1px solid rgba(187, 247, 208, 0.8);
        border-bottom-right-radius: 0.25rem;
        align-self: flex-end;
        box-shadow: 0 1px 3px rgba(2, 8, 20, 0.04);
        white-space: pre-wrap;
    }

    .chatbot-bubble.assistant {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 0.25rem;
        align-self: flex-start;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
    }

    /* Formatted Content inside Bubbles */
    .chatbot-content {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .chatbot-p {
        margin: 0;
        line-height: 1.55;
    }
    .chatbot-p strong,
    .chatbot-li strong {
        color: #0f172a;
        font-weight: 700;
    }

    .chatbot-ul,
    .chatbot-ol {
        margin: 0.15rem 0 0.25rem 1.25rem;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .chatbot-li {
        line-height: 1.5;
        padding-left: 0.15rem;
    }
    .chatbot-code {
        background: #f1f5f9;
        color: #0f172a;
        padding: 0.15rem 0.35rem;
        border-radius: 4px;
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.82em;
        border: 1px solid #e2e8f0;
    }

    /* Action Buttons Container */
    .chatbot-actions {
        margin-top: 0.65rem;
        padding-top: 0.55rem;
        border-top: 1px dashed #e2e8f0;
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
    }
    .chatbot-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #15803d;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        line-height: 1.25;
        box-shadow: 0 1px 2px rgba(22, 163, 74, 0.08);
        outline: none;
    }
    .chatbot-action-btn:hover {
        background: #16a34a;
        border-color: #16a34a;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    }
    .chatbot-action-btn:focus-visible {
        outline: 2px solid #16a34a;
        outline-offset: 2px;
    }
    .chatbot-action-btn:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(22, 163, 74, 0.1);
    }
    .chatbot-action-btn i.bi-check2 {
        font-size: 0.92rem;
        color: #16a34a;
        transition: color 0.18s ease;
    }
    .chatbot-action-btn:hover i.bi-check2 {
        color: #ffffff;
    }
    .chatbot-action-btn i.bi-arrow-right-short {
        font-size: 1.15rem;
        line-height: 1;
        transition: transform 0.18s ease;
    }
    .chatbot-action-btn:hover i.bi-arrow-right-short {
        transform: translateX(3px);
    }

    /* Typing indicator */
    .chatbot-typing-bubble {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.6rem 0.9rem;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 0.82rem;
    }
    .chatbot-typing-dots {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .chatbot-typing-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #16a34a;
        display: inline-block;
        animation: chatbotBounce 1.2s infinite ease-in-out;
    }
    .chatbot-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .chatbot-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    .chatbot-typing-dots span:nth-child(3) { animation-delay: 0s; }

    @keyframes chatbotBounce {
        0%, 80%, 100% { transform: scale(0); opacity: 0.4; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* Input area */
    .chatbot-input {
        padding: 0.75rem 1rem;
        background: #ffffff;
        border-top: 1px solid rgba(15, 23, 42, 0.08);
    }
    .chatbot-input-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 9999px;
        padding: 0.25rem 0.4rem 0.25rem 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .chatbot-input-wrap:focus-within {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        background: #ffffff;
    }
    #chatbotText {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.87rem;
        color: #0f172a;
        outline: none;
        padding: 0.35rem 0;
    }
    #chatbotText::placeholder {
        color: #94a3b8;
        font-size: 0.83rem;
    }
    .chatbot-send-btn {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        border: none;
        background: #15803d;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
        flex-shrink: 0;
    }
    .chatbot-send-btn:hover {
        background: #166534;
        transform: scale(1.05);
    }
    .chatbot-send-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
        transform: none;
    }
</style>

<script>
    (function () {
        const widget = document.getElementById('chatbotWidget');
        const panel = document.getElementById('chatbotPanel');
        const toggle = document.getElementById('chatbotToggle');
        const closeBtn = document.getElementById('chatbotClose');
        const list = document.getElementById('chatbotMessages');
        const form = document.getElementById('chatbotForm');
        const input = document.getElementById('chatbotText');
        const submitBtn = document.getElementById('chatbotSubmit');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const userRole = widget?.getAttribute('data-role') || 'guest';

        // Known friendly route labels
        const ROUTE_LABELS = {
            '/admin/dashboard': 'Dashboard',
            '/admin/student-verifications': 'Review Students',
            '/admin/landlord-verifications': 'Review Landlords',
            '/admin/approvals/landlords': 'Review Landlord Documents',
            '/admin/permits': 'Review Permits',
            '/admin/properties': 'View Properties',
            '/admin/properties/approval': 'Property Approvals',
            '/admin/bookings': 'View Bookings',
            '/admin/boarding-monitoring': 'Boarding Monitoring',
            '/admin/boarded-students': 'Boarded Students',
            '/admin/onboardings': 'View Onboardings',
            '/admin/reports': 'View Reports',
            '/admin/users': 'Manage Users',
            '/admin/settings': 'System Settings',
            '/admin/notifications': 'Notifications',
            '/student/dashboard': 'Dashboard',
            '/student/tenant-dashboard': 'Tenant Dashboard',
            '/student/rooms': 'Browse Rooms',
            '/student/properties/map': 'Property Map',
            '/student/requests': 'My Requests',
            '/student/bookings': 'My Bookings',
            '/student/onboarding': 'Tenant Onboarding',
            '/student/payments': 'My Payments',
            '/student/reports': 'My Reports',
            '/student/profile': 'My Profile',
            '/student/setup': 'Student Verification',
            '/student/notifications': 'Notifications',
            '/landlord/dashboard': 'Dashboard',
            '/landlord/setup': 'Landlord Setup',
            '/landlord/properties': 'My Properties',
            '/landlord/rooms': 'Manage Rooms',
            '/landlord/bookings': 'Booking Requests',
            '/landlord/tenants': 'My Tenants',
            '/landlord/onboarding': 'Tenant Onboardings',
            '/landlord/payments': 'Tenant Payments',
            '/landlord/leave-requests': 'Leave Requests',
            '/landlord/maintenance': 'Maintenance',
            '/landlord/analytics': 'Analytics',
            '/landlord/feedback': 'Tenant Feedback',
            '/landlord/notifications': 'Notifications',
            '/notifications': 'Notifications',
            '/messages': 'Messages',
        };

        function resolveRouteLabel(url) {
            if (!url) return 'View Details';
            const clean = url.split(/[?#]/)[0].trim();
            if (ROUTE_LABELS[clean]) return ROUTE_LABELS[clean];

            if (/^\/student\/rooms\/\d+$/.test(clean)) return 'View Room';
            if (/^\/admin\/properties\/\d+$/.test(clean)) return 'View Property';

            const parts = clean.split('/').filter(Boolean);
            const last = parts[parts.length - 1] || 'Open';
            return last.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }

        function isRouteAuthorized(url, role) {
            if (!url || typeof url !== 'string') return false;
            const clean = url.trim();
            if (!clean.startsWith('/') || clean.startsWith('//')) return false;
            if (/^(javascript:|data:|http:|https:|file:)/i.test(clean)) return false;

            const r = (role || '').toLowerCase();
            if (r === 'student') {
                return clean.startsWith('/student') || clean === '/notifications' || clean === '/messages';
            }
            if (r === 'landlord') {
                return clean.startsWith('/landlord') || clean === '/notifications' || clean === '/messages';
            }
            if (r === 'admin') {
                return clean.startsWith('/admin') || clean === '/notifications' || clean === '/messages';
            }
            return false;
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function parseMarkdownInline(escapedText) {
            return escapedText
                // **bold** or __bold__
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.+?)__/g, '<strong>$1</strong>')
                // *italic* or _italic_
                .replace(/\*([^*]+)\*/g, '<em>$1</em>')
                .replace(/_([^_]+)_/g, '<em>$1</em>')
                // inline `code`
                .replace(/`([^`]+)`/g, '<code class="chatbot-code">$1</code>');
        }

        function renderStructuredContent(rawText, actionsCollector) {
            let text = String(rawText || '');

            // Detect any raw internal routes in prose: e.g. /admin/student-verifications
            const pathRegex = /(?:(?:\s*(?:sa|at|in|to|visit)\s+)?(?:`|'|")?(\/(?:admin|student|landlord|notifications|messages)[a-zA-Z0-9_\-\/]*)(?:`|'|")?)/gi;
            text = text.replace(pathRegex, (match, path, offset, fullStr) => {
                if (isRouteAuthorized(path, userRole)) {
                    const label = resolveRouteLabel(path);
                    actionsCollector.push({
                        type: 'internal_route',
                        label: label,
                        route: path,
                        url: path,
                    });
                    const preceding = fullStr.slice(Math.max(0, offset - 40), offset);
                    if (preceding.toLowerCase().includes(label.toLowerCase()) || preceding.includes('**')) {
                        return '';
                    }
                    return ` **${label}**`;
                }
                return match;
            });
            text = text.replace(/\s+([.,!?])/g, '$1');

            const container = document.createElement('div');
            container.className = 'chatbot-content';

            const lines = text.split('\n');
            let currentList = null;
            let listType = null;
            let currentParagraph = [];

            function flushParagraph() {
                if (currentParagraph.length > 0) {
                    const p = document.createElement('p');
                    p.className = 'chatbot-p';
                    p.innerHTML = parseMarkdownInline(escapeHtml(currentParagraph.join('\n'))).replace(/\n/g, '<br>');
                    container.appendChild(p);
                    currentParagraph = [];
                }
            }

            function flushList() {
                if (currentList) {
                    container.appendChild(currentList);
                    currentList = null;
                    listType = null;
                }
            }

            for (let i = 0; i < lines.length; i++) {
                const line = lines[i];
                const trimmed = line.trim();

                if (!trimmed) {
                    flushParagraph();
                    flushList();
                    continue;
                }

                // Unordered list item: - item, * item, • item
                const ulMatch = trimmed.match(/^[-*•]\s+(.*)$/);
                if (ulMatch) {
                    flushParagraph();
                    if (!currentList || listType !== 'ul') {
                        flushList();
                        currentList = document.createElement('ul');
                        currentList.className = 'chatbot-ul';
                        listType = 'ul';
                    }
                    const li = document.createElement('li');
                    li.className = 'chatbot-li';
                    li.innerHTML = parseMarkdownInline(escapeHtml(ulMatch[1]));
                    currentList.appendChild(li);
                    continue;
                }

                // Ordered list item: 1. item, 2. item
                const olMatch = trimmed.match(/^(\d+)\.\s+(.*)$/);
                if (olMatch) {
                    flushParagraph();
                    if (!currentList || listType !== 'ol') {
                        flushList();
                        currentList = document.createElement('ol');
                        currentList.className = 'chatbot-ol';
                        listType = 'ol';
                    }
                    const li = document.createElement('li');
                    li.className = 'chatbot-li';
                    li.innerHTML = parseMarkdownInline(escapeHtml(olMatch[2]));
                    currentList.appendChild(li);
                    continue;
                }

                // Regular prose line
                flushList();
                currentParagraph.push(trimmed);
            }

            flushParagraph();
            flushList();

            return container;
        }

        function renderActionButtons(actionsList) {
            if (!actionsList || !actionsList.length) return null;

            const seen = new Set();
            const valid = [];

            actionsList.forEach((act) => {
                if (!act) return;

                if (act.type === 'geo') {
                    valid.push(act);
                    return;
                }

                const url = act.route || act.url;
                if (!url || seen.has(url)) return;
                if (!isRouteAuthorized(url, userRole)) return;

                seen.add(url);
                valid.push({
                    type: 'internal_route',
                    label: act.label || resolveRouteLabel(url),
                    route: url,
                    url: url,
                });
            });

            if (!valid.length) return null;

            const wrap = document.createElement('div');
            wrap.className = 'chatbot-actions';

            valid.forEach((act) => {
                if (act.type === 'geo') {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'chatbot-action-btn';
                    btn.innerHTML = `<i class="bi bi-geo-alt-fill"></i><span>${escapeHtml(act.label || 'Share location')}</span>`;
                    btn.addEventListener('click', () => {
                        window.dispatchEvent(new CustomEvent('chatbot:geo-request', { detail: { prompt: act.prompt } }));
                    });
                    wrap.appendChild(btn);
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'chatbot-action-btn';
                btn.setAttribute('data-url', act.route || act.url);
                btn.innerHTML = `<i class="bi bi-check2"></i><span>${escapeHtml(act.label)}</span><i class="bi bi-arrow-right-short"></i>`;
                btn.addEventListener('click', () => {
                    window.location.href = act.route || act.url;
                });
                wrap.appendChild(btn);
            });

            return wrap;
        }

        function addBubble(role, text, meta, directActions = []) {
            // Remove welcome block if user starts chatting
            const welcome = list.querySelector('.chatbot-welcome');
            if (welcome) welcome.remove();

            const wrap = document.createElement('div');
            wrap.className = `chatbot-bubble ${role}`;

            if (role === 'user') {
                wrap.textContent = text;
            } else {
                const actionsCollector = [];

                // Add direct actions or metadata actions
                if (Array.isArray(directActions) && directActions.length) {
                    actionsCollector.push(...directActions);
                } else if (meta && Array.isArray(meta.actions)) {
                    actionsCollector.push(...meta.actions);
                } else if (meta && meta.action) {
                    actionsCollector.push(meta.action);
                }

                const bodyNode = renderStructuredContent(text, actionsCollector);
                wrap.appendChild(bodyNode);

                const buttonsNode = renderActionButtons(actionsCollector);
                if (buttonsNode) {
                    wrap.appendChild(buttonsNode);
                }
            }

            list.appendChild(wrap);
            scrollToBottom();
        }

        function showTypingIndicator() {
            removeTypingIndicator();
            const typing = document.createElement('div');
            typing.className = 'chatbot-bubble assistant chatbot-typing-bubble';
            typing.id = 'chatbotTypingIndicator';
            typing.innerHTML = `
                <div class="chatbot-typing-dots">
                    <span></span><span></span><span></span>
                </div>
                <span class="chatbot-typing-label">Checking system...</span>
            `;
            list.appendChild(typing);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            const typing = document.getElementById('chatbotTypingIndicator');
            if (typing) typing.remove();
        }

        function scrollToBottom() {
            list.scrollTop = list.scrollHeight;
        }

        function getStoredLocation() {
            try {
                const raw = localStorage.getItem('chatbotGeo');
                if (!raw) return null;
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        }

        function storeLocation(lat, lng) {
            try {
                localStorage.setItem('chatbotGeo', JSON.stringify({ lat, lng }));
            } catch (e) {}
        }

        async function loadHistory() {
            try {
                const res = await fetch('/chatbot/history');
                if (!res.ok) return;
                const data = await res.json();
                list.innerHTML = '';
                const messages = data.messages || [];

                if (messages.length === 0) {
                    list.innerHTML = `
                        <div class="chatbot-welcome">
                            <div class="chatbot-welcome-icon">
                                <i class="bi bi-stars"></i>
                            </div>
                            <h6>Kumusta! How can I help you today?</h6>
                            <p>Maaari kang magtanong tungkol sa rooms, bookings, payments, reports, o system statistics.</p>
                        </div>
                    `;
                } else {
                    messages.forEach((msg) => {
                        const meta = msg.meta || {};
                        const actions = meta.actions || (meta.action ? [meta.action] : []);
                        addBubble(msg.role, msg.content, meta, actions);
                    });
                }
            } catch (e) {
                console.error('Failed to load chatbot history', e);
            }
        }

        async function sendMessage(text, extra = {}) {
            showTypingIndicator();
            if (submitBtn) submitBtn.disabled = true;
            if (input) input.disabled = true;

            try {
                const payload = { content: text, ...extra };
                const res = await fetch('/chatbot/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: JSON.stringify(payload),
                });

                removeTypingIndicator();

                if (res.status === 429) {
                    addBubble('assistant', 'Masyadong mabilis ang pagpapadala ng mensahe. Mangyaring maghintay ng ilang segundo bago sumubok muli.');
                    return;
                }

                if (!res.ok) {
                    addBubble('assistant', 'Sorry, I could not reach the assistant right now. Please try again shortly.');
                    return;
                }

                const data = await res.json();
                const replyText = data.reply || data.message || '';
                const actions = data.actions || data.meta?.actions || (data.meta?.action ? [data.meta.action] : []);
                addBubble('assistant', replyText, data.meta || {}, actions);
            } catch (e) {
                removeTypingIndicator();
                addBubble('assistant', 'Sorry, an unexpected connection issue occurred.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
                if (input) {
                    input.disabled = false;
                    input.focus();
                }
            }
        }

        toggle?.addEventListener('click', () => {
            panel.classList.toggle('open');
            panel.setAttribute('aria-hidden', panel.classList.contains('open') ? 'false' : 'true');
            if (panel.classList.contains('open')) {
                loadHistory();
                setTimeout(() => input?.focus(), 150);
            }
        });

        closeBtn?.addEventListener('click', () => {
            panel.classList.remove('open');
            panel.setAttribute('aria-hidden', 'true');
        });

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = (input.value || '').trim();
            if (!text) return;
            addBubble('user', text);
            input.value = '';

            const geo = getStoredLocation();
            const extra = geo ? { lat: geo.lat, lng: geo.lng } : {};
            await sendMessage(text, extra);
        });

        window.addEventListener('chatbot:geo-request', (event) => {
            const prompt = event.detail?.prompt || 'Hanapin ang pinakamalapit na rooms';
            if (!navigator.geolocation) {
                addBubble('assistant', 'Hindi available ang location sa browser na ito.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    storeLocation(lat, lng);
                    sendMessage(prompt, { lat, lng });
                },
                () => {
                    addBubble('assistant', 'Hindi ko nakuha ang location mo. Maaari mong i-type ang iyong gustong lugar.');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    })();
</script>
