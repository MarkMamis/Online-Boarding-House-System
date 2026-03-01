@php
    $chatbotRole = Auth::user()->role ?? 'guest';
@endphp

<div id="chatbotWidget" data-role="{{ $chatbotRole }}" class="chatbot-widget">
    <button type="button" class="chatbot-toggle" id="chatbotToggle" aria-label="Open chat">
        <i class="bi bi-chat-dots"></i>
    </button>

    <div class="chatbot-panel" id="chatbotPanel" aria-hidden="true">
        <div class="chatbot-header">
            <div>
                <div class="chatbot-title">Assistant</div>
                <div class="chatbot-subtitle">Role: {{ ucfirst($chatbotRole) }}</div>
            </div>
            <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Close chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages"></div>
        <div class="chatbot-input">
            <form id="chatbotForm">
                @csrf
                <input type="text" id="chatbotText" placeholder="Ask about this system..." maxlength="2000" required />
                <button type="submit" class="btn btn-brand btn-sm rounded-pill">Send</button>
            </form>
        </div>
    </div>
</div>

<style>
    .chatbot-widget { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 9999; }
    .chatbot-toggle {
        width: 52px; height: 52px; border-radius: 50%; border: none;
        background: var(--brand); color: #fff; box-shadow: 0 12px 30px rgba(2,8,20,.2);
        display: inline-flex; align-items: center; justify-content: center;
    }
    .chatbot-panel {
        position: absolute; right: 0; bottom: 64px; width: min(360px, 92vw);
        background: #fff; border: 1px solid rgba(2,8,20,.1); border-radius: 1rem;
        box-shadow: 0 20px 50px rgba(2,8,20,.2); display: none; flex-direction: column;
        overflow: hidden;
    }
    .chatbot-panel.open { display: flex; }
    .chatbot-header { padding: .85rem 1rem; background: rgba(2,8,20,.03); display: flex; align-items: center; justify-content: space-between; }
    .chatbot-title { font-weight: 700; }
    .chatbot-subtitle { font-size: .75rem; color: rgba(2,8,20,.55); }
    .chatbot-close { background: none; border: none; color: rgba(2,8,20,.55); }
    .chatbot-messages { padding: .85rem 1rem; max-height: 320px; overflow-y: auto; display: flex; flex-direction: column; gap: .6rem; }
    .chatbot-bubble { padding: .6rem .75rem; border-radius: .85rem; font-size: .85rem; line-height: 1.45; }
    .chatbot-bubble.user { background: rgba(22,101,52,.12); align-self: flex-end; }
    .chatbot-bubble.assistant { background: #f1f5f9; align-self: flex-start; }
    .chatbot-actions { margin-top: .35rem; }
    .chatbot-actions a { font-size: .78rem; }
    .chatbot-input { border-top: 1px solid rgba(2,8,20,.08); padding: .75rem 1rem; }
    #chatbotForm { display: flex; gap: .5rem; }
    #chatbotText { flex: 1; border: 1px solid rgba(2,8,20,.15); border-radius: 999px; padding: .4rem .75rem; font-size: .85rem; }
</style>
<script>
    (function () {
        const panel = document.getElementById('chatbotPanel');
        const toggle = document.getElementById('chatbotToggle');
        const closeBtn = document.getElementById('chatbotClose');
        const list = document.getElementById('chatbotMessages');
        const form = document.getElementById('chatbotForm');
        const input = document.getElementById('chatbotText');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function addBubble(role, text, meta) {
            const wrap = document.createElement('div');
            wrap.className = `chatbot-bubble ${role}`;
            wrap.textContent = text;

            if (meta && meta.action && meta.action.url) {
                const actionWrap = document.createElement('div');
                actionWrap.className = 'chatbot-actions';
                const link = document.createElement('a');
                link.href = meta.action.url;
                link.className = 'btn btn-outline-secondary btn-sm rounded-pill';
                link.textContent = meta.action.label || 'Open';
                actionWrap.appendChild(link);
                wrap.appendChild(actionWrap);
            }

            list.appendChild(wrap);
            list.scrollTop = list.scrollHeight;
        }

        async function loadHistory() {
            const res = await fetch('/chatbot/history');
            if (!res.ok) return;
            const data = await res.json();
            list.innerHTML = '';
            (data.messages || []).forEach(msg => {
                addBubble(msg.role, msg.content, msg.meta || {});
            });
        }

        toggle?.addEventListener('click', () => {
            panel.classList.toggle('open');
            panel.setAttribute('aria-hidden', panel.classList.contains('open') ? 'false' : 'true');
            if (panel.classList.contains('open')) loadHistory();
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

            const res = await fetch('/chatbot/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf || '',
                },
                body: JSON.stringify({ content: text }),
            });
            if (!res.ok) {
                addBubble('assistant', 'Sorry, I could not send that.');
                return;
            }
            const data = await res.json();
            addBubble('assistant', data.reply, data.meta || {});
        });
    })();
</script>
