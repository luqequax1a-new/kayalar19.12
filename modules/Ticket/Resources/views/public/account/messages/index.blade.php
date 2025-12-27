@extends('storefront::public.account.layout')

@section('title', trans('ticket::ticket.tickets'))

@section('account_breadcrumb')
    <li class="active">{{ trans('ticket::ticket.tickets') }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header d-flex justify-content-between align-items-center">
            <h4>{{ trans('ticket::ticket.tickets') }}</h4>
            <a href="{{ route('account.tickets.create') }}" class="btn btn-primary btn-sm">{{ trans('ticket::ticket.new_ticket') }}</a>
        </div>
        <div class="panel-body" style="padding:0">
            <style>
                .tickets-wrap{padding:12px}
                .tickets-table{width:100%;}
                .tickets-mobile{display:none}
                .status-badge{display:inline-block;font-size:13px;font-weight:600}
                .status-open{color:#0f5132}
                .status-closed{color:#842029}
                @media (max-width: 768px){
                    .tickets-wrap{padding:8px}
                    .tickets-table{display:none}
                    .tickets-mobile{display:grid;gap:10px}
                    .ticket-card{border:1px solid #e5e7eb;border-radius:10px;background:#fff;padding:12px;display:flex;flex-direction:column;gap:8px}
                    .ticket-card .row{display:flex;justify-content:space-between;align-items:center}
                    .ticket-card .title{font-weight:600}
                    .ticket-card .meta{font-size:12px;color:#666}
                }
            </style>
            <div class="tickets-wrap">
            @if ($tickets->isEmpty())
                <div class="empty-message">
                    <h3>{{ __('Henüz bir destek talebiniz yok.') }}</h3>
                    <a href="{{ route('account.tickets.create') }}" class="btn btn-primary" style="margin-top:10px;">{{ trans('ticket::ticket.new_ticket') }}</a>
                </div>
            @else
                <table class="table tickets-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>{{ trans('ticket::ticket.subject') }}</th>
                        <th>{{ trans('ticket::ticket.status') }}</th>
                        <th>{{ trans('ticket::ticket.last_message') }}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($tickets as $t)
                        <tr>
                            <td>{{ $t->id }}</td>
                            <td>{{ $t->subject }}</td>
                            <td>
                                <span class="status-badge {{ $t->status === 'closed' ? 'status-closed' : 'status-open' }}">
                                    {{ $t->status === 'closed' ? 'Kapalı' : ($t->status === 'waiting_admin' ? 'Admin Bekleniyor' : ($t->status === 'waiting_customer' ? 'Cevabınız Bekleniyor' : 'Açık')) }}
                                </span>
                            </td>
                            <td>{{ optional($t->last_message_at)->toDateTimeString() }}</td>
                            <td><a href="{{ route('account.tickets.show', $t->id) }}" class="btn btn-default btn-sm">{{ __('Görüntüle') }}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="tickets-mobile">
                    @foreach($tickets as $t)
                        <div class="ticket-card">
                            <div class="row"><span class="title">#{{ $t->id }} — {{ $t->subject }}</span><span class="status-badge {{ $t->status === 'closed' ? 'status-closed' : 'status-open' }}">{{ $t->status === 'closed' ? 'Kapalı' : ($t->status === 'waiting_admin' ? 'Admin Bekleniyor' : ($t->status === 'waiting_customer' ? 'Cevabınız Bekleniyor' : 'Açık')) }}</span></div>
                            <div class="meta">{{ optional($t->last_message_at)->toDateTimeString() }}</div>
                            <div class="row"><a href="{{ route('account.tickets.show', $t->id) }}" class="btn btn-default btn-sm">{{ __('Görüntüle') }}</a></div>
                        </div>
                    @endforeach
                </div>
            @endif
            </div>
        </div>
        <div class="panel-footer">
            {!! $tickets->links() !!}
        </div>
    </div>
@endsection
