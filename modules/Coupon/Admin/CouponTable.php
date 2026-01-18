<?php

namespace Modules\Coupon\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class CouponTable extends AdminTable
{
    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        return $this->newTable()
            ->addColumn('discount', function ($coupon) {
                return $coupon->is_percent
                    ? ((int) $coupon->value) . '%'
                    : $coupon->value->format();
            })
            ->addColumn('validity', function ($coupon) {
                $now = now();
                $startCarbon = $coupon->start_date;
                $endCarbon = $coupon->end_date;

                if ($startCarbon && $endCarbon) {
                    $start = $startCarbon->format('d.m.Y');
                    $end = $endCarbon->format('d.m.Y');
                    
                    if ($endCarbon->isFuture()) {
                        $daysLeft = (int) ceil($now->diffInDays($endCarbon, false));
                        return $start . ' - ' . $end . ' <span style="color: #10b981;">(' . $daysLeft . ' gün kaldı)</span>';
                    } else {
                        return $start . ' - ' . $end . ' <span style="color: #ef4444;">(Süresi doldu)</span>';
                    }
                }
                
                if ($endCarbon) {
                    $end = $endCarbon->format('d.m.Y');
                    
                    if ($endCarbon->isFuture()) {
                        $daysLeft = (int) ceil($now->diffInDays($endCarbon, false));
                        return 'Son: ' . $end . ' <span style="color: #10b981;">(' . $daysLeft . ' gün kaldı)</span>';
                    } else {
                        return 'Son: ' . $end . ' <span style="color: #ef4444;">(Süresi doldu)</span>';
                    }
                }
                
                if ($startCarbon) {
                    $start = $startCarbon->format('d.m.Y');
                    return 'Başlangıç: ' . $start . ' <span style="color: #64748b;">(Süresiz)</span>';
                }
                
                return '<span style="color: #64748b;">Süresiz</span>';
            })
            ->editColumn('status', function ($coupon) {
                if (!$coupon->is_active) {
                    return '<span class="badge badge-danger">Pasif</span>';
                }

                if ($coupon->usageLimitReached()) {
                    return '<span class="badge badge-warning" style="background-color: #f39c12; color: #fff; font-weight: 600;">Limit Doldu</span>';
                }

                return '<span class="badge badge-success">Aktif</span>';
            })
            ->addColumn('usage', function ($coupon) {
                $used = (int) $coupon->used;
                $limit = $coupon->usage_limit_per_coupon ?? '∞';
                
                $html = '<div style="white-space: nowrap;">Genel: ' . $used . ' / ' . $limit . '</div>';
                
                if ($coupon->usage_limit_per_customer) {
                    $html .= '<div style="font-size: 11px; color: #64748b;">Müşteri Limiti: ' . $coupon->usage_limit_per_customer . '</div>';
                }
                
                return $html;
            })
            ->addColumn('customer', function ($coupon) {
                if ($coupon->customer_id && $coupon->customer) {
                    $fullName = trim($coupon->customer->first_name . ' ' . $coupon->customer->last_name);
                    return e($fullName) . ' <br><small style="color: #64748b;">(' . e($coupon->customer->email) . ')</small>';
                }
                return '<span style="color: #94a3b8;">Genel Kupon</span>';
            })
            ->rawColumns(['validity', 'status', 'customer', 'usage']);
    }
}
