<div class="premium-card">
    <div class="premium-card-header" style="background: #fdfdfd; padding: 20px 25px;">
        <div style="display: flex; gap: 40px; justify-content: flex-start; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px; min-width: 140px;">
                <div class="premium-icon-box" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;">
                    <i class="fa fa-shopping-basket"></i>
                </div>
                <div>
                    <span style="display: block; color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase;">Siparişler</span>
                    <span style="display: block; color: #0f172a; font-size: 16px; font-weight: 800;">{{ $user->orders()->count() }} Adet</span>
                </div>
            </div>
            <div style="height: 30px; width: 1px; background: #f1f5f9;"></div>
            <div style="display: flex; align-items: center; gap: 12px; min-width: 140px;">
                <div class="premium-icon-box" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                    <i class="fa fa-money"></i>
                </div>
                <div>
                    <span style="display: block; color: #94a3b8; font-size: 10px; font-weight: 800; text-transform: uppercase;">Toplam Ciro</span>
                    <span style="display: block; color: #0f172a; font-size: 16px; font-weight: 800;" class="text-mono">
                        {{ \Modules\Support\Money::inDefaultCurrency($user->orders()->sum('total'))->format() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="insight-badge" style="font-size: 10px; opacity: 0.8;">
            SON SİPARİŞ: {{ $user->orders()->latest()->first()?->created_at?->diffForHumans() ?? '-' }}
        </div>
    </div>

    <div class="box-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th style="width: 18%;">Sipariş No</th>
                        <th style="width: 20%;">Tarih</th>
                        <th style="width: 17%; text-align: center;">Durum</th>
                        <th style="width: 25%;">Ödeme</th>
                        <th style="width: 10%; text-align: center;">Miktar</th>
                        <th style="width: 15%; text-align: right;">Toplam</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->orders()->latest()->take(10)->get() as $order)
                        <tr class="premium-row">
                            <td class="text-mono" style="font-weight: 700; color: #475569;">{{ $order->displayOrderNumber() }}</td>
                            <td style="color: #64748b; font-size: 12px;">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td style="text-align: center;">
                                <span class="badge-premium" style="min-width: 100px; background: {{ str_replace(['badge-success','badge-info','badge-warning','badge-danger','badge-secondary','badge-primary'], ['rgba(16,185,129,0.08)','rgba(59,130,246,0.08)','rgba(245,158,11,0.08)','rgba(239,68,68,0.08)','rgba(100,116,139,0.08)','rgba(99,102,241,0.08)'], order_status_badge_class($order->status)) }}; color: {{ str_replace(['badge-success','badge-info','badge-warning','badge-danger','badge-secondary','badge-primary'], ['#10b981','#3b82f6','#f59e0b','#ef4444','#64748b','#6366f1'], order_status_badge_class($order->status)) }};">
                                    {{ trans("order::statuses.{$order->status}") }}
                                </span>
                            </td>
                            <td style="color: #475569; font-size: 13px;">{{ $order->payment_method }}</td>
                            <td style="text-align: center;">
                                <span class="insight-badge" style="min-width: 30px;">{{ $order->products->count() }}</span>
                            </td>
                            <td class="text-mono" style="text-align: right; font-weight: 800; color: #0f172a; font-size: 14px;">
                                {{ $order->total->format() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 40px 0; color: #94a3b8; letter-spacing: 0.5px;">
                                <i class="fa fa-info-circle" style="display: block; font-size: 24px; margin-bottom: 10px; opacity: 0.3;"></i>
                                HENÜZ SİPARİŞ KAYDI BULUNMUYOR
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
