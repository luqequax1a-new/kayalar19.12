<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Destek Talebi #{{ $ticket->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            color: #334155;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }
        .header-sub {
            margin-top: 8px;
            opacity: 0.9;
            font-size: 15px;
            font-weight: 500;
        }
        .content {
            padding: 40px 30px;
        }
        .info-card { 
            background: #f8fafc; 
            border-radius: 16px; 
            padding: 24px; 
            border: 1px solid #e2e8f0; 
            margin-bottom: 24px;
        }
        .info-row {
            margin-bottom: 12px;
            display: flex;
            align-items: baseline;
        }
        .info-row:last-child { margin-bottom: 0; }
        .info-label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            width: 100px;
            flex-shrink: 0;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }
        .message-box {
            margin-top: 24px;
        }
        .message-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 12px;
            display: block;
        }
        .message-content {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            white-space: pre-wrap;
        }
        .btn-wrap { 
            margin-top: 32px; 
            text-align: center;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff !important;
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.2s;
        }
        .footer {
            text-align: center;
            padding: 24px;
            font-size: 13px;
            color: #94a3b8;
            background-color: #f1f5f9;
        }
        @media screen and (max-width: 600px) {
            .container { margin-top: 0; border-radius: 0; }
            .content { padding: 30px 20px; }
            .header { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            {{-- HEADER --}}
            <div class="header">
                <h1>Yeni Destek Talebi 🎫</h1>
                <div class="header-sub">Ticket #{{ $ticket->id }}</div>
                @if($ticket->source === 'contact')
                    <div style="margin-top:12px;display:inline-block;background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;border:1.5px solid rgba(255,255,255,0.3);">
                        📧 İletişim Formundan Gönderildi
                    </div>
                @endif
            </div>

            <div class="content">
                {{-- INFO CARD --}}
                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">Konu:</span>
                        <span class="info-value">{{ $ticket->subject }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Kategori:</span>
                        <span class="info-value">{{ $ticket->category ?: 'Genel' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gönderen:</span>
                        <span class="info-value">
                            @if($ticket->user)
                                {{ $ticket->user->full_name }}
                            @else
                                {{ $ticket->guest_email }}
                            @endif
                        </span>
                    </div>
                    @if($ticket->order_id)
                        <div class="info-row">
                            <span class="info-label">Sipariş:</span>
                            <span class="info-value" style="background:#e0e7ff;color:#4338ca;padding:4px 10px;border-radius:6px;display:inline-block;">
                                📦 #{{ $ticket->order->displayOrderNumber() }}
                            </span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Tarih:</span>
                        <span class="info-value">{{ $ticket->created_at->translatedFormat('d F Y H:i') }}</span>
                    </div>
                </div>

                {{-- MESSAGE CONTENT --}}
                <div class="message-box">
                    <span class="message-label">Mesaj İçeriği</span>
                    <div class="message-content">{{ $ticket->messages->first()?->body ?? 'Mesaj içeriği yüklenemedi' }}</div>
                </div>

                {{-- ACTION BUTTON --}}
                <div class="btn-wrap">
                    <a href="{{ route('admin.tickets.show', $ticket->id) }}" class="btn">
                        Ticket'ı Görüntüle ve Yanıtla &rarr;
                    </a>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="footer">
                &copy; {{ date('Y') }} {{ setting('store_name') }}. Bu e-posta otomatik olarak gönderilmiştir.
            </div>
        </div>
    </div>
</body>
</html>
