<div class="premium-card">
    <div class="premium-card-header">
        <h3 class="premium-card-title">
            <div class="premium-icon-box" style="background: rgba(99, 102, 241, 0.08); color: #6366f1;">
                <i class="fa fa-envelope"></i>
            </div>
            Son e-postalar
        </h3>
        <span class="insight-badge">{{ $user->emails()->count() }} İleti</span>
    </div>
    <div class="box-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table premium-table">
                <thead>
                    <tr>
                        <th style="width: 35%;">Tarih</th>
                        <th style="width: 65%;">Konu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->emails()->latest()->take(8)->get() as $email)
                        <tr class="premium-row">
                            <td style="color: #64748b; font-size: 11px;">
                                <i class="fa fa-clock-o" style="opacity: 0.4; margin-right: 4px;"></i>
                                {{ $email->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td style="font-weight: 600; color: #334155; font-size: 12px;" title="{{ $email->subject }}">
                                {{ $email->subject }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center" style="padding: 40px 0; color: #94a3b8; font-size: 12px;">
                                E-posta kaydı bulunmuyor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
