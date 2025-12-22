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
                .chat-wrapper{width:100%;margin:0;padding:12px}
                .chat-card{background:#fff;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.10);overflow:hidden;border:1px solid #eef2f7}
                .chat-container{display:flex;flex-direction:column;min-height:70vh;background:#f8fafc}
                .chat-header{position:sticky;top:0;z-index:5;padding:14px 16px;border-bottom:1px solid #eef2f7;background:rgba(255,255,255,.9);backdrop-filter:blur(10px);min-height:64px}
                .chat-wrap{padding:16px;display:flex;flex-direction:column;gap:12px;overflow:auto}
                .msg{max-width:78%;padding:12px 14px;border-radius:16px;position:relative;line-height:1.6;font-size:14px;box-shadow:0 6px 18px rgba(15,23,42,.06)}
                .msg-user{margin-left:auto;background:linear-gradient(135deg,#4f46e5,#6d28d9);border:1px solid rgba(255,255,255,.15);color:#fff}
                .msg-admin{margin-right:auto;background:#ffffff;border:1px solid #e2e8f0;color:#0f172a}
                .msg-author{font-size:12px;font-weight:600;opacity:.9;margin-bottom:6px}
                .msg-group{display:flex;flex-direction:column;gap:6px;max-width:78%}
                .msg-group-user{margin-left:auto;align-items:flex-end}
                .msg-group-admin{margin-right:auto;align-items:flex-start}
                .bubble{width:100%}
                .bubble .attachments{margin-top:0}
                .msg-attach{background:transparent;border:none;box-shadow:none;padding:0;margin:0}
                .attachments{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin:10px 0 0}
                .attachments .item{border-radius:12px;overflow:hidden;border:1px solid rgba(226,232,240,.9);background:#fff}
                .attachments .item img{display:block;width:100%;height:120px;object-fit:cover}
                .attachments-user{justify-content:flex-end}
                .attachments-admin{justify-content:flex-start}
                .msg-time{font-size:11px;opacity:.75;margin-top:8px;text-align:right}
                .chat-composer{padding:12px;border-top:1px solid #eef2f7;background:#fff}
                .chat-composer-inner{display:grid;grid-template-columns:1fr 44px;gap:10px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;padding:10px;width:100%;align-items:end}
                .chat-composer .field{flex:1;position:relative}
                .chat-composer .actions{display:flex;gap:8px;align-items:center}
                .icon-btn{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border:1px solid #e5e7eb;border-radius:8px;background:#fff}
                .send-btn{height:44px;width:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;background:#4f46e5;color:#fff;border:none}
                .file-count{font-size:12px;color:#64748b}
                .file-input{display:none}
                .chat-composer textarea{min-height:44px;max-height:220px;resize:none;border:none;outline:none;padding:10px 12px;overflow-y:auto;width:100%;word-break:break-word;background:transparent;font-size:14px;line-height:1.6;color:#0f172a}
                .composer-top{display:flex;gap:10px;align-items:center;margin-bottom:12px}
                .upload-btn{display:inline-flex;gap:6px;align-items:center;padding:8px 12px;border:1px solid #e2e8f0;border-radius:12px;background:#fff;cursor:pointer;color:#0f172a}
                .status-chip{display:inline-flex;align-items:center;gap:8px;font-size:12px;padding:6px 10px;border-radius:9999px;border:1px solid #e2e8f0;background:#fff;color:#0f172a}
                .dot{width:8px;height:8px;border-radius:50%}
                .dot-waiting-admin{background:#f59e0b}
                .dot-waiting-customer{background:#3b82f6}
                .dot-open{background:#10b981}
                .dot-closed{background:#ef4444}
                @media (max-width: 768px){
                    .chat-wrapper{width:100%;padding:8px}
                    .chat-container{min-height:calc(100vh - 160px)}
                    .chat-wrap{padding:12px}
                    .msg{max-width:92%}
                    .attachments{grid-template-columns:repeat(auto-fill,minmax(120px,1fr))}
                    .attachments .item img{height:110px}
                    .chat-composer{padding:8px}
                    .chat-composer textarea{min-height:40px;max-height:160px}
                    .send-btn{height:40px;width:40px}
                }
            </style>
            <div class="chat-wrapper"><div class="chat-card"><div class="chat-container">
                <div class="chat-header d-flex justify-content-between align-items-center">
                    <div></div>
                    <div class="status-chip">
                        @php($s = (string) $ticket->status)
                        <span class="dot {{ $s === 'closed' ? 'dot-closed' : ($s === 'waiting_admin' ? 'dot-waiting-admin' : ($s === 'waiting_customer' ? 'dot-waiting-customer' : 'dot-open')) }}"></span>
                        <span>
                            {{ $s === 'closed' ? 'Kapalı' : ($s === 'waiting_admin' ? 'Admin Bekleniyor' : ($s === 'waiting_customer' ? 'Cevabınız Bekleniyor' : 'Açık')) }}
                        </span>
                    </div>
                </div>
                <div class="chat-wrap">
                    @foreach($ticket->messages as $m)
                        @if($m->is_internal)
                            @continue
                        @endif
                        <div class="msg-group {{ $m->sender_type === 'admin' ? 'msg-group-admin' : 'msg-group-user' }}">
                            <div class="msg-author">
                                {{ $m->sender_type === 'admin' ? 'Admin' : optional(auth()->user())->full_name }}
                            </div>

                            @if($m->attachments->isNotEmpty())
                                <div class="msg {{ $m->sender_type === 'admin' ? 'msg-admin' : 'msg-user' }} bubble">
                                    <div class="attachments">
                                        @foreach($m->attachments as $a)
                                            <div class="item">
                                                <a href="{{ $a->url }}" class="ticket-lightbox" data-gallery="ticket-{{ $ticket->id }}">
                                                    <img src="{{ $a->url }}" alt="attachment">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(trim((string) $m->body) !== '')
                                <div class="msg {{ $m->sender_type === 'admin' ? 'msg-admin' : 'msg-user' }} bubble">
                                    <div>{{ $m->body }}</div>
                                </div>
                            @endif

                            <div class="msg-time">{{ optional($m->created_at)->format('Y-m-d H:i') }}</div>
                        </div>
                    @endforeach
                </div>
                @if($ticket->status !== 'closed')
                    <form method="POST" action="{{ route('account.tickets.messages.store', $ticket->id) }}" enctype="multipart/form-data" class="chat-composer">
                        {{ csrf_field() }}
                        <div class="composer-top">
                            <input id="ticket-upload" type="file" name="images[]" multiple accept="image/*" class="file-input">
                            <label for="ticket-upload" class="upload-btn"><i class="las la-image"></i> {{ __('Görsel ekle') }}</label>
                            <span class="file-count"></span>
                        </div>
                        <div class="chat-composer-inner">
                            <div class="field">
                                <textarea id="ticket-textarea" name="body" rows="1" placeholder="{{ trans('ticket::ticket.write_message') }}" required></textarea>
                            </div>
                            <button class="btn btn-primary send-btn"><i class="las la-paper-plane"></i></button>
                        </div>
                    </form>
                @else
                    <div class="chat-composer" style="opacity:.7">
                        <div class="chat-composer-inner">
                            <div class="field">
                                <textarea rows="1" placeholder="{{ __('Ticket kapalı, mesaj gönderemezsiniz.') }}" disabled></textarea>
                            </div>
                            <button class="btn btn-default send-btn" disabled><i class="las la-paper-plane"></i></button>
                        </div>
                    </div>
                @endif
            </div></div></div>
        </div>
    </div>
@endsection
@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded',function(){
            if (window.GLightbox) {
                GLightbox({ selector: '.ticket-lightbox' });
            }
            var wrap = document.querySelector('.chat-wrap');
            if (wrap) { wrap.scrollTop = wrap.scrollHeight; }
            var fi = document.getElementById('ticket-upload');
            var fc = document.querySelector('.file-count');
            if (fi && fc) {
                fi.addEventListener('change', function(){
                    fc.textContent = fi.files && fi.files.length ? (fi.files.length + ' dosya') : '';
                });
            }
            var ta = document.getElementById('ticket-textarea');
            if (ta) {
                var auto = function(){
                    ta.style.height = 'auto';
                    var h = Math.min(ta.scrollHeight, 220);
                    ta.style.height = h + 'px';
                    ta.style.overflowY = ta.scrollHeight > 220 ? 'auto' : 'hidden';
                };
                ta.addEventListener('input', auto);
                auto();

                var form = document.querySelector('.chat-composer');
                ta.addEventListener('keydown', function(e){
                    if (e.key === 'Enter' && !e.shiftKey && form) {
                        e.preventDefault();
                        form.submit();
                    }
                });
            }
        });
    </script>
@endpush
