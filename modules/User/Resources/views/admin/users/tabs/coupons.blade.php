<div class="premium-card">
    <div class="premium-card-header">
        <h3 class="premium-card-title">
            <div class="premium-icon-box" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                <i class="fa fa-tags"></i>
            </div>
            Kuponlar
        </h3>
        <span class="insight-badge">{{ $user->coupons()->count() }} Adet</span>
    </div>
    <div class="box-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Kod</th>
                        <th style="width: 25%;">İndirim</th>
                        <th style="width: 40%;">Kullanım</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->coupons()->latest()->take(8)->get() as $coupon)
                        @php
                            $used = $coupon->timesUsedByCustomer($user->email);
                            $limit = $coupon->usage_limit_per_customer;
                            $percent = $limit ? min(($used / $limit) * 100, 100) : 0;
                            $barColor = $percent >= 100 ? '#ef4444' : ($percent >= 80 ? '#f59e0b' : '#10b981');
                        @endphp
                        <tr class="premium-row">
                            <td class="text-mono" style="font-weight: 700;">
                                <span style="background: #f8fafc; padding: 3px 8px; border-radius: 4px; border: 1px solid #eef2f7;">{{ $coupon->code }}</span>
                            </td>
                            <td style="font-weight: 700; color: #0f172a;">
                                {{ $coupon->is_percent ? '%' . (int) $coupon->value : $coupon->value->format() }}
                            </td>
                            <td>
                                <div class="progress-compact-wrapper">
                                    <div class="progress-compact">
                                        <div class="progress-compact-bar" style="width: {{ $limit ? $percent : 0 }}%; background: {{ $barColor }};"></div>
                                    </div>
                                    <span class="text-mono" style="font-size: 10px; font-weight: 700; color: #64748b;">{{ $used }}/{{ $limit ?? '∞' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center" style="padding: 40px 0; color: #94a3b8; font-size: 12px;">
                                Tanımlı kupon bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
