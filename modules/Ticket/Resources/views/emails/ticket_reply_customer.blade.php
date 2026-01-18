<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Destek Talebinize Yanıt Verildi #{{ $ticket->id }}</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .intro-text {
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .message-box { 
            background: #f8fafc; 
            border-radius: 16px; 
            padding: 24px; 
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
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
            background-color: #10b981;
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
                <h1>Destek Ekibinden Yanıt Var! 💬</h1>
                <div class="header-sub">Ticket #{{ $ticket->id }}</div>
            </div>

            <div class="content">
                <div class="greeting">Merhaba{{ $ticket->user ? ' ' . $ticket->user->first_name : '' }},</div>
                <div class="intro-text">
                    <strong>"{{ $ticket->subject }}"</strong> konulu destek talebinize yanıt verildi.
                    @if($ticket->order_id)
                        <div style="margin-top:12px;">
                            <span style="background:#e0e7ff;color:#4338ca;padding:6px 12px;border-radius:8px;font-size:13px;font-weight:600;display:inline-block;">
                                📦 Sipariş #{{ $ticket->order->displayOrderNumber() }} ile ilgili
                            </span>
                        </div>
                    @endif
                </div>

                {{-- MESSAGE BOX --}}
                <div class="message-box">
                    <span class="message-label">Gelen Yanıt</span>
                    <div class="message-content">{{ $ticketMessage->body }}</div>
                </div>

                {{-- ACTION BUTTON --}}
                <div class="btn-wrap">
                    <a href="{{ route('account.tickets.show', $ticket->id) }}" class="btn">
                        Ticket'ı Görüntüle ve Cevapla &rarr;
                    </a>
                </div>

                <div style="text-align: center; margin-top: 24px; font-size: 13px; color: #64748b;">
                    Başka sorularınız varsa bu maili cevaplamadan, yukarıdaki butonu kullanarak ticket üzerinden devam edebilirsiniz.
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="footer">
                &copy; {{ date('Y') }} {{ setting('store_name') }}. Bize ulaştığınız için teşekkür ederiz.
            </div>
        </div>
    </div>
</body>
</html>
