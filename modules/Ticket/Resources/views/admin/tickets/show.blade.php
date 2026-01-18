@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('ticket::ticket.ticket'))
    <li><a href="{{ route('admin.tickets.index') }}">{{ trans('ticket::ticket.tickets') }}</a></li>
    <li class="active">#{{ $ticket->id }}</li>
@endcomponent

@section('content')
<div class="panel">
    <div class="panel-body" style="padding:0">
        <style>
            .modern-admin-chat{display:flex;flex-direction:column;height:calc(100vh - 280px);min-height:650px;background:#fff}
            .admin-chat-header{padding:20px 24px;border-bottom:1.5px solid #f3f4f6;background:#fff;display:flex;justify-content:space-between;align-items:center}
            .ticket-info{flex:1}
            .ticket-id-subject{font-size:16px;font-weight:700;color:#111827;margin-bottom:4px}
            .customer-email{font-size:13px;color:#6b7280}
            .header-actions{display:flex;gap:12px;align-items:center}
            .status-badge-admin{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid}
            .status-waiting-admin{background:#fef3c7;border-color:#fbbf24;color:#92400e}
            .status-waiting-customer{background:#dbeafe;border-color:#60a5fa;color:#1e40af}
            .status-closed{background:#fee2e2;border-color:#f87171;color:#991b1b}
            .status-open{background:#d1fae5;border-color:#34d399;color:#065f46}
            .status-dot-admin{width:6px;height:6px;border-radius:50%;background:currentColor}
            .close-btn-admin{padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;background:#fff;border:1.5px solid #e5e7eb;color:#6b7280;cursor:pointer;transition:all 0.2s}
            .close-btn-admin:hover{border-color:#ef4444;color:#ef4444;background:#fef2f2}
            .admin-messages-area{flex:1;overflow-y:auto;padding:24px;background:#f9fafb;display:flex;flex-direction:column;gap:16px}
            .admin-message-group{display:flex;flex-direction:column;gap:8px;max-width:75%}
            .admin-message-group-customer{align-self:flex-start;align-items:flex-start}
            .admin-message-group-admin{align-self:flex-end;align-items:flex-end}
            .admin-message-sender{font-size:12px;font-weight:600;color:#6b7280;margin-bottom:4px;padding:0 4px}
            .admin-message-bubble{padding:14px 16px;border-radius:12px;font-size:14px;line-height:1.6;word-wrap:break-word;box-shadow:0 2px 8px rgba(0,0,0,0.04)}
            .admin-message-bubble-customer{background:#fff;color:#111827;border:1.5px solid #e5e7eb;border-bottom-left-radius:4px}
            .admin-message-bubble-admin{background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border-bottom-right-radius:4px}
            .admin-message-images{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:8px;margin-top:8px}
            .admin-message-image{border-radius:8px;overflow:hidden;cursor:pointer}
            .admin-message-image *{border:none !important;outline:none !important;box-shadow:none !important}
            .admin-message-image img{display:block;width:100%;height:140px;object-fit:cover;transition:transform 0.2s}
            .admin-message-image:hover img{transform:scale(1.02)}
            .admin-message-time{font-size:11px;color:#9ca3af;margin-top:4px;padding:0 4px}
            .admin-composer{padding:20px 24px;border-top:1.5px solid #f3f4f6;background:#fff}
            .admin-composer-upload{margin-bottom:12px;display:flex;gap:10px;align-items:center}
            .admin-upload-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid #e5e7eb;border-radius:8px;background:#fff;color:#6b7280;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s}
            .admin-upload-btn:hover{border-color:#6366f1;color:#6366f1;background:#f0f1ff}
            .admin-file-count{font-size:12px;color:#9ca3af}
            .admin-composer-input-area{display:flex;gap:12px;align-items:flex-end;padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;background:#f9fafb;transition:all 0.2s}
            .admin-composer-input-area:focus-within{border-color:#6366f1;background:#fff;box-shadow:0 0 0 4px rgba(99,102,241,0.08)}
            .admin-composer-textarea{flex:1;border:none;background:transparent;outline:none;resize:none;min-height:44px;max-height:200px;font-size:14px;color:#111827;font-family:inherit;line-height:1.6;padding:8px}
            .admin-composer-textarea::placeholder{color:#9ca3af}
            .admin-send-btn{width:44px;height:44px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#4f46e5);color:#fff;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.2s;box-shadow:0 4px 12px rgba(99,102,241,0.3)}
            .admin-send-btn:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(99,102,241,0.4)}
            @media(max-width:768px){
                .modern-admin-chat{height:calc(100vh - 200px);min-height:500px}
                .admin-chat-header{padding:16px 20px;flex-direction:column;align-items:flex-start;gap:12px}
                .admin-messages-area{padding:16px}
                .admin-message-group{max-width:85%}
                .admin-composer{padding:16px 20px}
            }
        </style>
        
        <div class="modern-admin-chat">
            <div class="admin-chat-header">
                <div class="ticket-info">
                    <div class="ticket-id-subject">#{{ $ticket->id }} — {{ $ticket->subject }}</div>
                    <div class="customer-email">
                        @if(optional($ticket->user)->email || $ticket->guest_email)
                            {{ optional($ticket->user)->email ?: $ticket->guest_email }}
                        @endif
                        
                        @if($ticket->order_id)
                            <span style="margin-left:12px;padding:4px 8px;background:#e0e7ff;color:#4338ca;border-radius:6px;font-size:12px;font-weight:600;">
                                📦 Sipariş: <a href="{{ route('admin.orders.show', $ticket->order_id) }}" style="color:#4338ca;text-decoration:underline;" target="_blank">#{{ $ticket->order->displayOrderNumber() }}</a>
                            </span>
                        @endif
                        
                        @if($ticket->source === 'contact')
                            <span style="margin-left:8px;padding:4px 8px;background:#fef3c7;color:#92400e;border-radius:6px;font-size:12px;font-weight:600;">
                                📧 İletişim Formu
                            </span>
                        @endif
                    </div>
                </div>
                <div class="header-actions">
                    <div class="status-badge-admin status-{{ str_replace('_', '-', $ticket->status) }}">
                        <span class="status-dot-admin"></span>
                        @php($s = (string) $ticket->status)
                        {{ $s === 'closed' ? 'Kapalı' : ($s === 'waiting_admin' ? 'Admin Bekleniyor' : ($s === 'waiting_customer' ? 'Müşteri Bekleniyor' : 'Açık')) }}
                    </div>
                    <form method="POST" action="{{ route('admin.tickets.close', $ticket->id) }}" style="display:inline">
                        {{ csrf_field() }}
                        <button type="submit" class="close-btn-admin">Ticket'ı Kapat</button>
                    </form>
                </div>
            </div>

            <div class="admin-messages-area" id="adminMessagesArea">
                @foreach($ticket->messages as $m)
                    @if($m->is_internal)
                        @continue
                    @endif
                    <div class="admin-message-group admin-message-group-{{ $m->sender_type === 'admin' ? 'admin' : 'customer' }}">
                        <div class="admin-message-sender">
                            @if($m->sender_type === 'admin')
                                Destek Ekibi
                            @else
                                {{ optional($ticket->user)->full_name ?: ($ticket->guest_email ?: 'Müşteri') }}
                            @endif
                        </div>

                        @if($m->attachments->isNotEmpty())
                            <div class="admin-message-images">
                                @foreach($m->attachments as $a)
                                    <div class="admin-message-image">
                                        <a href="{{ $a->url }}" class="ticket-lightbox" data-gallery="ticket-{{ $ticket->id }}">
                                            <img src="{{ $a->url }}" alt="attachment">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(trim((string) $m->body) !== '')
                            <div class="admin-message-bubble admin-message-bubble-{{ $m->sender_type === 'admin' ? 'admin' : 'customer' }}">
                                {{ $m->body }}
                            </div>
                        @endif

                        <div class="admin-message-time">{{ optional($m->created_at)->format('d.m.Y H:i') }}</div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('admin.tickets.messages.store', $ticket->id) }}" enctype="multipart/form-data" class="admin-composer">
                {{ csrf_field() }}
                <div class="admin-composer-upload">
                    <input id="adminTicketUpload" type="file" name="images[]" multiple accept="image/*" style="display:none">
                    <label for="adminTicketUpload" class="admin-upload-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        Görsel Ekle
                    </label>
                    <span class="admin-file-count" id="adminFileCount"></span>
                </div>
                <div class="admin-composer-input-area">
                    <textarea id="adminTicketTextarea" name="body" class="admin-composer-textarea" placeholder="Müşteriye cevap yazın..." required></textarea>
                    <button type="submit" class="admin-send-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            const messagesArea = document.getElementById('adminMessagesArea');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }

            // File upload
            const fileInput = document.getElementById('adminTicketUpload');
            const fileCount = document.getElementById('adminFileCount');
            if (fileInput && fileCount) {
                fileInput.addEventListener('change', function() {
                    fileCount.textContent = this.files.length > 0 ? `${this.files.length} dosya seçildi` : '';
                });
            }

            // Textarea auto-resize
            const textarea = document.getElementById('adminTicketTextarea');
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
@endsection
