@extends('storefront::public.account.layout')

@section('title', trans('ticket::ticket.ticket'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.tickets.index') }}">{{ trans('ticket::ticket.tickets') }}</a></li>
    <li class="active">#{{ $ticket->id }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header">
            <h4>#{{ $ticket->id }} — {{ $ticket->subject }}</h4>
        </div>
        <div class="panel-body" style="padding:0">
            <style>
                .modern-chat{display:flex;flex-direction:column;height:calc(100vh - 280px);min-height:600px;background:#fff}
                .chat-header-modern{padding:20px 24px;border-bottom:1.5px solid #f3f4f6;background:#fff;display:flex;justify-content:space-between;align-items:center}
                .chat-title{font-size:15px;font-weight:600;color:#111827}
                .status-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid}
                .status-waiting-admin{background:#fef3c7;border-color:#fbbf24;color:#92400e}
                .status-waiting-customer{background:#dbeafe;border-color:#60a5fa;color:#1e40af}
                .status-closed{background:#fee2e2;border-color:#f87171;color:#991b1b}
                .status-open{background:#d1fae5;border-color:#34d399;color:#065f46}
                .status-dot{width:6px;height:6px;border-radius:50%;background:currentColor}
                .messages-area{flex:1;overflow-y:auto;padding:24px;background:#f9fafb;display:flex;flex-direction:column;gap:16px}
                .message-group{display:flex;flex-direction:column;gap:8px;max-width:75%}
                .message-group-user{align-self:flex-end;align-items:flex-end}
                .message-group-admin{align-self:flex-start;align-items:flex-start}
                .message-sender{font-size:12px;font-weight:600;color:#6b7280;margin-bottom:4px;padding:0 4px}
                .message-bubble{padding:14px 16px;border-radius:12px;font-size:14px;line-height:1.6;word-wrap:break-word;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
                .message-bubble-user{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-bottom-right-radius:4px}
                .message-bubble-admin{background:#fff;color:#111827;border:1.5px solid #e5e7eb;border-bottom-left-radius:4px}
                .message-images{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-top:8px}
                .message-image{border-radius:8px;overflow:hidden;cursor:pointer}
                .message-image *{border:none !important;outline:none !important;box-shadow:none !important}
                .message-image img{display:block;width:100%;height:140px;object-fit:cover;transition:transform 0.2s}
                .message-image:hover img{transform:scale(1.02)}
                .message-time{font-size:11px;color:#9ca3af;margin-top:4px;padding:0 4px}
                .composer-modern{padding:20px 24px;border-top:1.5px solid #f3f4f6;background:#fff}
                .composer-upload{margin-bottom:12px;display:flex;gap:10px;align-items:center}
                .upload-btn-modern{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#6b7280;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s}
                .upload-btn-modern:hover{border-color:#6366f1;color:#6366f1;background:#f0f1ff}
                .file-count-modern{font-size:12px;color:#9ca3af}
                .composer-input-area{display:flex;gap:12px;align-items:flex-end;padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;background:#f9fafb;transition:all 0.2s}
                .composer-input-area:focus-within{border-color:#6366f1;background:#fff;box-shadow:0 0 0 4px rgba(99,102,241,0.08)}
                .composer-textarea{flex:1;border:none;background:transparent;outline:none;resize:none;min-height:44px;max-height:200px;font-size:14px;color:#111827;font-family:inherit;line-height:1.6;padding:8px}
                .composer-textarea::placeholder{color:#9ca3af}
                .send-btn-modern{width:44px;height:44px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 12px rgba(99,102,241,0.3)}
                .send-btn-modern:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(99,102,241,0.4)}
                .send-btn-modern:disabled{opacity:0.5;cursor:not-allowed;transform:none}
                @media(max-width:768px){
                    .modern-chat{height:calc(100vh - 200px);min-height:500px}
                    .chat-header-modern{padding:16px 20px}
                    .messages-area{padding:16px}
                    .message-group{max-width:85%}
                    .composer-modern{padding:16px 20px}
                }
            </style>
            
            <div class="modern-chat">
                <div class="chat-header-modern">
                    <div class="chat-title">
                        {{ $ticket->subject }}
                        
                        @if($ticket->order_id)
                            <div style="margin-top:6px;">
                                <span style="padding:4px 10px;background:#e0e7ff;color:#4338ca;border-radius:6px;font-size:12px;font-weight:600;">
                                    📦 Sipariş: <a href="{{ route('account.orders.show', $ticket->order_id) }}" style="color:#4338ca;text-decoration:underline;">Sipariş #{{ $ticket->order->displayOrderNumber() }}</a>
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="status-badge status-{{ str_replace('_', '-', $ticket->status) }}">
                        <span class="status-dot"></span>
                        @php($s = (string) $ticket->status)
                        {{ $s === 'closed' ? 'Kapalı' : ($s === 'waiting_admin' ? 'Admin Bekleniyor' : ($s === 'waiting_customer' ? 'Cevabınız Bekleniyor' : 'Açık')) }}
                    </div>
                </div>

                <div class="messages-area" id="messagesArea">
                    @foreach($ticket->messages as $m)
                        @if($m->is_internal)
                            @continue
                        @endif
                        <div class="message-group message-group-{{ $m->sender_type === 'admin' ? 'admin' : 'user' }}">
                            <div class="message-sender">
                                {{ $m->sender_type === 'admin' ? 'Destek Ekibi' : 'Siz' }}
                            </div>

                            @if($m->attachments->isNotEmpty())
                                <div class="message-images">
                                    @foreach($m->attachments as $a)
                                        <div class="message-image">
                                            <a href="{{ $a->url }}" class="ticket-lightbox" data-gallery="ticket-{{ $ticket->id }}">
                                                <img src="{{ $a->url }}" alt="attachment">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(trim((string) $m->body) !== '')
                                <div class="message-bubble message-bubble-{{ $m->sender_type === 'admin' ? 'admin' : 'user' }}">
                                    {{ $m->body }}
                                </div>
                            @endif

                            <div class="message-time">{{ optional($m->created_at)->format('d.m.Y H:i') }}</div>
                        </div>
                    @endforeach
                </div>

                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('account.tickets.messages.store', $ticket->id) }}" enctype="multipart/form-data" class="composer-modern">
                        {{ csrf_field() }}
                        <div class="composer-upload">
                            <input id="ticketUpload" type="file" name="images[]" multiple accept="image/*" style="display:none">
                            <label for="ticketUpload" class="upload-btn-modern">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                Görsel Ekle
                            </label>
                            <span class="file-count-modern" id="fileCount"></span>
                        </div>
                        <div class="composer-input-area">
                            <textarea id="ticketTextarea" name="body" class="composer-textarea" placeholder="Mesajınızı yazın..." required></textarea>
                            <button type="submit" class="send-btn-modern">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="composer-modern" style="opacity:0.6">
                        <div class="composer-input-area">
                            <textarea class="composer-textarea" placeholder="Ticket kapalı, mesaj gönderemezsiniz." disabled></textarea>
                            <button class="send-btn-modern" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lightbox
            if (window.GLightbox) {
                GLightbox({ selector: '.ticket-lightbox' });
            }

            // Scroll to bottom
            const messagesArea = document.getElementById('messagesArea');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }

            // File upload
            const fileInput = document.getElementById('ticketUpload');
            const fileCount = document.getElementById('fileCount');
            if (fileInput && fileCount) {
                fileInput.addEventListener('change', function() {
                    fileCount.textContent = this.files.length > 0 ? `${this.files.length} dosya seçildi` : '';
                });
            }

            // Textarea auto-resize
            const textarea = document.getElementById('ticketTextarea');
            if (textarea) {
                const autoResize = function() {
                    textarea.style.height = 'auto';
                    const newHeight = Math.min(textarea.scrollHeight, 200);
                    textarea.style.height = newHeight + 'px';
                };
                textarea.addEventListener('input', autoResize);

                // Enter to submit (Shift+Enter for new line)
                const form = textarea.closest('form');
                textarea.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey && form) {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }
        });
    </script>
@endpush
