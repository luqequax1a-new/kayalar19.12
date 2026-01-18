@extends('storefront::public.account.layout')

@section('title', trans('ticket::ticket.new_ticket'))

@section('account_breadcrumb')
    <li><a href="{{ route('account.tickets.index') }}">{{ trans('ticket::ticket.tickets') }}</a></li>
    <li class="active">{{ trans('ticket::ticket.new_ticket') }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h4>{{ trans('ticket::ticket.new_ticket') }}</h4>
        </div>
        <div class="panel-body" style="padding:0">
            <style>
                .modern-ticket-form{max-width:100%;margin:0;padding:32px 24px;background:#fff}
                .form-section{margin-bottom:28px}
                .form-section:last-of-type{margin-bottom:0}
                .section-label{font-size:13px;font-weight:600;color:#1f2937;margin-bottom:10px;display:block;letter-spacing:0.3px}
                .form-control-modern{width:100%;padding:13px 16px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:15px;color:#111827;background:#fff;transition:all 0.2s ease;font-family:inherit}
                .form-control-modern:focus{outline:none;border-color:#6366f1;box-shadow:0 0 0 4px rgba(99,102,241,0.08)}
                .form-control-modern::placeholder{color:#9ca3af}
                select.form-control-modern{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:40px}
                textarea.form-control-modern{resize:vertical;min-height:160px;line-height:1.6;font-family:inherit}
                .upload-zone{border:2px dashed #d1d5db;border-radius:8px;padding:20px;text-align:center;background:#f9fafb;transition:all 0.2s;cursor:pointer;margin-bottom:28px}
                .upload-zone:hover{border-color:#6366f1;background:#f0f1ff}
                .upload-zone-icon{width:48px;height:48px;margin:0 auto 12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px}
                .upload-zone-text{font-size:14px;color:#6b7280;font-weight:500}
                .upload-zone-subtext{font-size:12px;color:#9ca3af;margin-top:4px}
                .file-list{margin-top:12px;display:flex;flex-wrap:wrap;gap:8px}
                .file-item{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#f3f4f6;border-radius:6px;font-size:13px;color:#374151}
                .file-item-remove{cursor:pointer;color:#ef4444;font-weight:bold;margin-left:4px}
                .form-actions{display:flex;gap:12px;justify-content:flex-end;padding-top:24px;border-top:1.5px solid #f3f4f6}
                .btn-modern{height:48px;padding:0 32px;border-radius:8px;font-size:15px;font-weight:600;border:none;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px;justify-content:center}
                .btn-primary-modern{background:linear-gradient(135deg,#6366f1 0%,#4f46e5 100%);color:#fff;box-shadow:0 4px 14px rgba(99,102,241,0.35)}
                .btn-primary-modern:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,0.45)}
                .btn-secondary-modern{background:#fff;color:#6b7280;border:1.5px solid #e5e7eb}
                .btn-secondary-modern:hover{border-color:#d1d5db;color:#374151}
                @media(max-width:768px){
                    .modern-ticket-form{padding:20px 16px}
                    .form-section{margin-bottom:24px}
                    .btn-modern{height:44px;padding:0 24px;font-size:14px}
                }
            </style>
            
            <div class="modern-ticket-form">
                <form method="POST" action="{{ route('account.tickets.store') }}" enctype="multipart/form-data" id="ticketForm">
                    {{ csrf_field() }}
                    
                    <div class="form-section">
                        <label class="section-label">Konu</label>
                        <input 
                            type="text" 
                            name="subject" 
                            class="form-control-modern" 
                            placeholder="Örn: Sipariş takibi, ürün iadesi, teknik destek..." 
                            required 
                            value="{{ old('subject') }}"
                        >
                    </div>

                    <div class="form-section">
                        <label class="section-label">Kategori</label>
                        <select name="category" class="form-control-modern">
                            <option value="">Kategori seçin (isteğe bağlı)</option>
                            <option value="Genel Sorular">💬 Genel Sorular</option>
                            <option value="Sipariş Takibi">📦 Sipariş Takibi</option>
                            <option value="İade ve Değişim">🔄 İade ve Değişim</option>
                            <option value="Ürün Bilgisi">🏷️ Ürün Bilgisi</option>
                            <option value="Ödeme Sorunları">💳 Ödeme Sorunları</option>
                            <option value="Teknik Destek">🔧 Teknik Destek</option>
                            <option value="Diğer">📌 Diğer</option>
                        </select>
                    </div>

                    @php
                        $userOrders = auth()->check() 
                            ? \Modules\Order\Entities\Order::where('customer_id', auth()->id())
                                ->orderByDesc('created_at')
                                ->limit(20)
                                ->get()
                            : collect();
                    @endphp

                    @if($userOrders->isNotEmpty())
                        <div class="form-section">
                            <label class="section-label">Sipariş (İsteğe Bağlı)</label>
                            <select name="order_id" class="form-control-modern">
                                <option value="">Sipariş seçin (isteğe bağlı)</option>
                                @foreach($userOrders as $order)
                                    <option value="{{ $order->id }}">
                                        📦 Sipariş #{{ $order->displayOrderNumber() }} - {{ $order->created_at->format('d.m.Y') }} - {{ $order->total->format() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="form-section">
                        <label class="section-label">Mesajınız</label>
                        <textarea 
                            name="body" 
                            class="form-control-modern" 
                            placeholder="Sorununuzu veya talebinizi detaylı olarak açıklayın..." 
                            required
                        >{{ old('body') }}</textarea>
                    </div>

                    <div class="upload-zone" onclick="document.getElementById('fileInput').click()">
                        <div class="upload-zone-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                        </div>
                        <div class="upload-zone-text">Görsel eklemek için tıklayın</div>
                        <div class="upload-zone-subtext">PNG, JPG, WEBP - Maks. 5MB</div>
                        <input type="file" id="fileInput" name="images[]" multiple accept="image/*" style="display:none">
                        <div class="file-list" id="fileList"></div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('account.tickets.index') }}" class="btn-modern btn-secondary-modern">
                            Vazgeç
                        </a>
                        <button type="submit" class="btn-modern btn-primary-modern">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Gönder
                        </button>
                    </div>
                </form>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const fileInput = document.getElementById('fileInput');
                    const fileList = document.getElementById('fileList');
                    const uploadZone = document.querySelector('.upload-zone');

                    fileInput.addEventListener('change', function() {
                        fileList.innerHTML = '';
                        if (this.files.length > 0) {
                            Array.from(this.files).forEach((file, index) => {
                                const fileItem = document.createElement('div');
                                fileItem.className = 'file-item';
                                fileItem.innerHTML = `
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                        <polyline points="13 2 13 9 20 9"></polyline>
                                    </svg>
                                    ${file.name}
                                `;
                                fileList.appendChild(fileItem);
                            });
                        }
                    });

                    // Drag and drop
                    uploadZone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        this.style.borderColor = '#6366f1';
                        this.style.background = '#f0f1ff';
                    });

                    uploadZone.addEventListener('dragleave', function(e) {
                        e.preventDefault();
                        this.style.borderColor = '#d1d5db';
                        this.style.background = '#f9fafb';
                    });

                    uploadZone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        this.style.borderColor = '#d1d5db';
                        this.style.background = '#f9fafb';
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    });
                });
            </script>
        </div>
    </div>
@endsection
