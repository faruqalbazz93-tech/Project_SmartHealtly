<?php
use App\Config\App;
use App\Core\Session;
use App\Core\Auth;
$puskesmas = App::puskesmas();
?>

<!-- ============================================================== -->
<!-- SmartHealthy Chatbot AI Floating Widget                        -->
<!-- ============================================================== -->
<div id="smarthealthy-chatbot-root" data-base-url="<?= App::baseUrl('') ?>" data-csrf="<?= Session::csrfToken() ?>">
    <!-- Floating Trigger Button -->
    <button id="chatbot-toggle-btn" class="btn btn-primary rounded-circle shadow-lg chatbot-float-btn d-flex align-items-center justify-content-center" 
            type="button" aria-label="Buka Chatbot AI SmartHealthy" title="SmartHealthy AI Assistant">
        <i class="bi bi-robot fs-3 chatbot-icon-open"></i>
        <i class="bi bi-x-lg fs-3 chatbot-icon-close d-none"></i>
        <span class="chatbot-pulse-ring"></span>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger chatbot-badge-ai">
            AI
            <span class="visually-hidden">Fitur AI Aktif</span>
        </span>
    </button>

    <!-- Chatbot Window Panel -->
    <div id="chatbot-panel" class="chatbot-card shadow-2xl d-none">
        <!-- Header -->
        <div class="chatbot-header d-flex align-items-center justify-content-between p-3 text-white">
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <div class="chatbot-avatar bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                        <i class="bi bi-heart-pulse-fill fs-5"></i>
                    </div>
                    <span class="chatbot-online-dot"></span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold fs-6">SmartHealthy AI Assistant</h6>
                    <small class="text-white-50 d-block" style="font-size: 0.72rem;">
                        <i class="bi bi-hospital me-1"></i><?= htmlspecialchars($puskesmas['name']) ?>
                    </small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" id="chatbot-btn-clear" class="btn btn-sm btn-link text-white-50 p-1 text-decoration-none hover-white" title="Hapus Riwayat Chat">
                    <i class="bi bi-trash3 fs-6"></i>
                </button>
                <button type="button" id="chatbot-btn-minimize" class="btn btn-sm btn-link text-white p-1 text-decoration-none hover-white" title="Tutup Chat">
                    <i class="bi bi-x-lg fs-6"></i>
                </button>
            </div>
        </div>

        <!-- Chat Conversation Messages Area -->
        <div id="chatbot-messages" class="chatbot-messages-body p-3">
            <!-- Welcome Bot Message -->
            <div class="chatbot-msg-row bot-row mb-3">
                <div class="chatbot-avatar-msg">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="chatbot-bubble bot-bubble">
                    <div class="chatbot-bubble-content">
                        Halo! Saya asisten SmartHealthy. Saya dapat membantu informasi layanan, jadwal Puskesmas, status pendaftaran Anda, dan edukasi kesehatan.
                    </div>
                    <div class="chatbot-time"><?= date('H:i') ?></div>
                </div>
            </div>

            <!-- Quick Action Chips (Tombol Pertanyaan Cepat) -->
            <div id="chatbot-quick-chips" class="chatbot-chips-wrap mb-3">
                <div class="small fw-semibold text-muted mb-2 px-1" style="font-size: 0.75rem;">
                    <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pertanyaan Cepat:
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill chatbot-chip" data-question="Lihat jadwal layanan">
                    📅 Lihat jadwal layanan
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill chatbot-chip" data-question="Cek pendaftaran saya">
                    🎫 Cek pendaftaran saya
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill chatbot-chip" data-question="Informasi layanan kesehatan">
                    🏥 Informasi layanan kesehatan
                </button>
            </div>

            <!-- Typing Indicator (Hidden by default) -->
            <div id="chatbot-typing" class="chatbot-msg-row bot-row mb-2 d-none">
                <div class="chatbot-avatar-msg">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="chatbot-bubble bot-bubble py-2 px-3">
                    <div class="chatbot-typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Input Form -->
        <div class="chatbot-footer p-2 bg-white border-top">
            <form id="chatbot-form" class="m-0" autocomplete="off">
                <div class="input-group">
                    <input type="text" id="chatbot-input" class="form-control border-end-0 rounded-start-pill ps-3" 
                           placeholder="Ketik pertanyaan Anda..." maxlength="500" required>
                    <button type="submit" id="chatbot-send-btn" class="btn btn-primary rounded-end-pill px-3" title="Kirim Pesan">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1 px-2">
                    <small class="text-muted" style="font-size: 0.68rem;">
                        <i class="bi bi-shield-check text-success me-1"></i>Bukan pengganti dokter &bull; Darurat: 119
                    </small>
                    <span id="chatbot-char-count" class="text-muted" style="font-size: 0.65rem;">0/500</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Riwayat -->
    <div class="modal fade" id="chatbotClearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Hapus Riwayat Chat?</h6>
                    <p class="small text-muted mb-4">Seluruh riwayat percakapan Anda dengan asisten AI akan dihapus permanen dari sistem.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-light px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="chatbot-confirm-clear-btn" class="btn btn-sm btn-danger px-3">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- Chatbot Styling (Vanilla CSS)                                  -->
<!-- ============================================================== -->
<style>
/* Floating Trigger Button */
.chatbot-float-btn {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 60px;
    height: 60px;
    z-index: 1060;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
}

.chatbot-float-btn:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 12px 28px rgba(2, 132, 199, 0.45) !important;
}

.chatbot-pulse-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid #0284c7;
    animation: chatbot-pulse 2.2s infinite ease-out;
    pointer-events: none;
}

@keyframes chatbot-pulse {
    0% { transform: scale(0.95); opacity: 0.9; }
    50% { transform: scale(1.35); opacity: 0; }
    100% { transform: scale(1.4); opacity: 0; }
}

.chatbot-badge-ai {
    font-size: 0.65rem;
    padding: 0.25em 0.5em;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Chatbot Main Window */
.chatbot-card {
    position: fixed;
    bottom: 96px;
    right: 24px;
    width: 390px;
    max-width: calc(100vw - 32px);
    height: 570px;
    max-height: calc(100vh - 120px);
    z-index: 1060;
    border-radius: 20px;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: chatbot-slide-up 0.25s ease-out;
}

@keyframes chatbot-slide-up {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.chatbot-header {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);
}

.chatbot-avatar {
    width: 36px;
    height: 36px;
}

.chatbot-online-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 11px;
    height: 11px;
    background-color: #10b981;
    border: 2px solid #ffffff;
    border-radius: 50%;
}

/* Messages Body */
.chatbot-messages-body {
    flex: 1;
    overflow-y: auto;
    background-color: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chatbot-messages-body::-webkit-scrollbar {
    width: 5px;
}
.chatbot-messages-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

/* Message Rows */
.chatbot-msg-row {
    display: flex;
    gap: 8px;
    max-width: 88%;
}

.bot-row {
    align-self: flex-start;
}

.user-row {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.chatbot-avatar-msg {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #e0f2fe;
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.chatbot-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    font-size: 0.86rem;
    line-height: 1.45;
    word-break: break-word;
    position: relative;
}

.bot-bubble {
    background-color: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-top-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.user-bubble {
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    color: #ffffff;
    border-top-right-radius: 4px;
    box-shadow: 0 2px 5px rgba(2, 132, 199, 0.25);
}

.chatbot-time {
    font-size: 0.65rem;
    color: #94a3b8;
    margin-top: 4px;
    text-align: right;
}

.user-bubble .chatbot-time {
    color: rgba(255, 255, 255, 0.7);
}

/* Markdown formatting inside bubbles */
.chatbot-bubble-content a {
    color: #0284c7;
    font-weight: 600;
    text-decoration: underline;
}
.user-bubble .chatbot-bubble-content a {
    color: #ffffff;
}

/* Quick Chips */
.chatbot-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.chatbot-chip {
    font-size: 0.76rem;
    padding: 4px 10px;
    background-color: #ffffff;
    border-color: #bae6fd;
    color: #0369a1;
    transition: all 0.2s ease;
}

.chatbot-chip:hover {
    background-color: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Typing Indicator */
.chatbot-typing-dots {
    display: flex;
    align-items: center;
    gap: 4px;
    height: 14px;
}

.chatbot-typing-dots span {
    width: 6px;
    height: 6px;
    background-color: #0284c7;
    border-radius: 50%;
    animation: chatbot-bounce 1.4s infinite ease-in-out both;
}

.chatbot-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
.chatbot-typing-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes chatbot-bounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1.0); }
}

/* Mobile Responsiveness */
@media (max-width: 576px) {
    .chatbot-card {
        bottom: 0;
        right: 0;
        width: 100vw;
        height: 100vh;
        max-width: 100vw;
        max-height: 100vh;
        border-radius: 0;
    }
    .chatbot-float-btn {
        bottom: 16px;
        right: 16px;
    }
}
</style>

<!-- ============================================================== -->
<!-- Chatbot Javascript Logic                                       -->
<!-- ============================================================== -->
<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const root = document.getElementById('smarthealthy-chatbot-root');
        if (!root) return;

        const baseUrl = root.dataset.baseUrl || '';
        let csrfToken = root.dataset.csrf || '';

        const toggleBtn = document.getElementById('chatbot-toggle-btn');
        const panel = document.getElementById('chatbot-panel');
        const iconOpen = toggleBtn.querySelector('.chatbot-icon-open');
        const iconClose = toggleBtn.querySelector('.chatbot-icon-close');
        const minimizeBtn = document.getElementById('chatbot-btn-minimize');
        const clearBtn = document.getElementById('chatbot-btn-clear');
        const confirmClearBtn = document.getElementById('chatbot-confirm-clear-btn');
        const clearModalEl = document.getElementById('chatbotClearModal');
        const clearModal = clearModalEl && window.bootstrap ? new bootstrap.Modal(clearModalEl) : null;

        const messagesContainer = document.getElementById('chatbot-messages');
        const typingIndicator = document.getElementById('chatbot-typing');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const sendBtn = document.getElementById('chatbot-send-btn');
        const charCount = document.getElementById('chatbot-char-count');
        const quickChips = document.getElementById('chatbot-quick-chips');

        let isSending = false;
        let historyLoaded = false;

        // Toggle Open/Close Chat Panel
        function toggleChat() {
            const isHidden = panel.classList.contains('d-none');
            if (isHidden) {
                panel.classList.remove('d-none');
                iconOpen.classList.add('d-none');
                iconClose.classList.remove('d-none');
                scrollToBottom();
                input.focus();

                if (!historyLoaded) {
                    loadChatHistory();
                }
            } else {
                panel.classList.add('d-none');
                iconOpen.classList.remove('d-none');
                iconClose.classList.add('d-none');
            }
        }

        toggleBtn.addEventListener('click', toggleChat);
        minimizeBtn.addEventListener('click', toggleChat);

        // Character Counter
        input.addEventListener('input', function() {
            const len = this.value.length;
            charCount.textContent = `${len}/500`;
            if (len >= 480) {
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-danger');
            }
        });

        // Quick Action Chips
        document.querySelectorAll('.chatbot-chip').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                if (question && !isSending) {
                    input.value = question;
                    charCount.textContent = `${question.length}/500`;
                    sendMessage(question);
                }
            });
        });

        // Form Submit
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const text = input.value.trim();
            if (text && !isSending) {
                sendMessage(text);
            }
        });

        // Send Message Handler
        async function sendMessage(userText) {
            if (userText.length < 2 || isSending) return;

            isSending = true;
            input.value = '';
            charCount.textContent = '0/500';
            input.disabled = true;
            sendBtn.disabled = true;

            // Hide quick chips once user starts conversation
            if (quickChips) quickChips.classList.add('d-none');

            // Render User Bubble
            renderMessage('user', userText, getCurrentTime());
            showTyping(true);
            scrollToBottom();

            try {
                const fd = new FormData();
                fd.append('message', userText);
                fd.append('_csrf_token', csrfToken);

                const response = await fetch(`${baseUrl}/api/chat/send`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });

                const data = await response.json();
                showTyping(false);

                if (response.ok && data.success) {
                    renderMessage('assistant', data.message, data.time || getCurrentTime());
                } else {
                    const errMsg = data.message || 'Maaf, terjadi kendala saat memproses pesan Anda.';
                    renderMessage('assistant', `⚠️ **Perhatian**: ${errMsg}`, getCurrentTime());
                }
            } catch (err) {
                showTyping(false);
                renderMessage('assistant', '⚠️ Terjadi kesalahan jaringan. Silakan periksa koneksi internet Anda dan coba lagi.', getCurrentTime());
            } finally {
                isSending = false;
                input.disabled = false;
                sendBtn.disabled = false;
                input.focus();
                scrollToBottom();
            }
        }

        // Load History
        async function loadChatHistory() {
            try {
                const res = await fetch(`${baseUrl}/api/chat/history`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await res.json();

                if (result.success) {
                    if (result.csrf_token) {
                        csrfToken = result.csrf_token;
                    }

                    if (result.messages && result.messages.length > 0) {
                        if (quickChips) quickChips.classList.add('d-none');
                        result.messages.forEach(function(msg) {
                            renderMessage(msg.role, msg.message, msg.time_formatted);
                        });
                        scrollToBottom();
                    }
                    historyLoaded = true;
                }
            } catch (e) {
                console.warn('Gagal memuat riwayat percakapan chatbot:', e);
            }
        }

        // Clear Chat History Modal & Trigger
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (clearModal) {
                    clearModal.show();
                } else if (confirm('Apakah Anda yakin ingin menghapus seluruh riwayat percakapan Anda?')) {
                    executeClearChat();
                }
            });
        }

        if (confirmClearBtn) {
            confirmClearBtn.addEventListener('click', function() {
                if (clearModal) clearModal.hide();
                executeClearChat();
            });
        }

        async function executeClearChat() {
            try {
                const fd = new FormData();
                fd.append('_csrf_token', csrfToken);

                const res = await fetch(`${baseUrl}/api/chat/clear`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });

                const data = await res.json();
                if (data.success) {
                    // Reset UI to welcome message
                    resetChatUi();
                } else {
                    alert(data.message || 'Gagal menghapus riwayat.');
                }
            } catch (err) {
                alert('Gagal menghubungi server untuk menghapus riwayat.');
            }
        }

        function resetChatUi() {
            const rows = messagesContainer.querySelectorAll('.chatbot-msg-row:not(:first-child)');
            rows.forEach(function(r) {
                if (r !== typingIndicator) r.remove();
            });
            if (quickChips) quickChips.classList.remove('d-none');
            scrollToBottom();
        }

        // Render Message Bubble safely using textContent and sanitized DOM nodes
        function renderMessage(role, rawContent, timeStr) {
            const row = document.createElement('div');
            row.className = `chatbot-msg-row ${role === 'user' ? 'user-row' : 'bot-row'} mb-3`;

            const avatar = document.createElement('div');
            avatar.className = 'chatbot-avatar-msg';
            const icon = document.createElement('i');
            icon.className = role === 'user' ? 'bi bi-person-fill' : 'bi bi-robot';
            avatar.appendChild(icon);

            const bubble = document.createElement('div');
            bubble.className = `chatbot-bubble ${role === 'user' ? 'user-bubble' : 'bot-bubble'}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'chatbot-bubble-content';

            if (role === 'user') {
                // Strict textContent for user messages prevents any HTML/script injection
                contentDiv.textContent = rawContent;
            } else {
                // Safe tokenizer for bot responses using document.createTextNode and safe elements
                renderSafeFormattedContent(contentDiv, rawContent);
            }

            const timeDiv = document.createElement('div');
            timeDiv.className = 'chatbot-time';
            timeDiv.textContent = timeStr || getCurrentTime();

            bubble.appendChild(contentDiv);
            bubble.appendChild(timeDiv);

            row.appendChild(avatar);
            row.appendChild(bubble);

            // Insert before typing indicator
            messagesContainer.insertBefore(row, typingIndicator);
        }

        // Safe DOM tokenizer for bold, links, bullet points, and line breaks
        function renderSafeFormattedContent(container, text) {
            if (!text) return;

            const lines = text.split('\n');
            lines.forEach(function(line, lineIdx) {
                if (lineIdx > 0) {
                    container.appendChild(document.createElement('br'));
                }

                let processedLine = line;
                const bulletMatch = processedLine.match(/^[•\-*]\s+(.*)$/);
                if (bulletMatch) {
                    const bulletSpan = document.createElement('span');
                    bulletSpan.textContent = '• ';
                    bulletSpan.className = 'text-primary fw-bold';
                    container.appendChild(bulletSpan);
                    processedLine = bulletMatch[1];
                }

                // Tokenize for bold **...** and markdown links [text](url)
                const tokenRegex = /(\*\*.*?\*\*|\[.*?\]\(.*?\))/g;
                let lastIndex = 0;
                let match;

                while ((match = tokenRegex.exec(processedLine)) !== null) {
                    if (match.index > lastIndex) {
                        const plain = processedLine.substring(lastIndex, match.index);
                        container.appendChild(document.createTextNode(plain));
                    }

                    const token = match[0];
                    if (token.startsWith('**') && token.endsWith('**')) {
                        const boldEl = document.createElement('strong');
                        boldEl.textContent = token.slice(2, -2);
                        container.appendChild(boldEl);
                    } else if (token.startsWith('[') && token.includes('](') && token.endsWith(')')) {
                        const splitIdx = token.indexOf('](');
                        const label = token.slice(1, splitIdx);
                        const href = token.slice(splitIdx + 2, -1).trim();

                        const linkEl = document.createElement('a');
                        linkEl.textContent = label;
                        if (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('/')) {
                            linkEl.href = href;
                        } else {
                            linkEl.href = '#' + href;
                        }
                        if (href.startsWith('http')) {
                            linkEl.target = '_blank';
                            linkEl.rel = 'noopener noreferrer';
                        }
                        container.appendChild(linkEl);
                    }

                    lastIndex = tokenRegex.lastIndex;
                }

                if (lastIndex < processedLine.length) {
                    const remaining = processedLine.substring(lastIndex);
                    container.appendChild(document.createTextNode(remaining));
                }
            });
        }

        function showTyping(show) {
            if (show) {
                typingIndicator.classList.remove('d-none');
            } else {
                typingIndicator.classList.add('d-none');
            }
        }

        function scrollToBottom() {
            setTimeout(function() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }, 60);
        }

        function getCurrentTime() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            return `${h}:${m}`;
        }
    });
})();
</script>
