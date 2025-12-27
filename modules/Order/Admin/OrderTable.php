<?php

namespace Modules\Order\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Exceptions\Exception;

class OrderTable extends AdminTable
{
    protected array $rawColumns = ['checkbox', 'order_no', 'order_count', 'status', 'actions', 'created'];
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $defaultRawColumns = [
        'status',
    ];

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function make()
    {
        $statuses = trans('order::statuses');

        return $this->newTable()
            ->addColumn('checkbox', function ($order) {
                return view('admin::partials.table.checkbox', ['entity' => $order]);
            })
            ->addColumn('customer_email', function ($order) {
                return $order->customer_email;
            })
            ->editColumn('order_no', function ($order) {
                return '<div class="order-no-wrapper" style="display: flex; align-items: center; gap: 8px;">
                            <span class="details-control" style="cursor: pointer; color: #3b82f6; display: flex;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </span>
                            <span>' . $order->displayOrderNumber() . '</span>
                        </div>';
            })
            ->addColumn('customer_name', function ($order) {
                return $order->customer_full_name;
            })
            ->addColumn('order_count', function ($order) {
                $count = \Modules\Order\Entities\Order::where('customer_email', $order->customer_email)
                    ->where('created_at', '<=', $order->created_at)
                    ->count();

                return "<span style='font-weight: 500; color: #475569;'>{$count}. Sipariş</span>";
            })
            ->editColumn('payment_method', function ($order) {
                return $order->payment_method;
            })
            ->editColumn('total', function ($order) {
                return $order->total->format();
            })
            ->editColumn('status', function ($order) use ($statuses) {
                $options = '';
                foreach ($statuses as $key => $label) {
                    $selected = $order->status === $key ? 'selected' : '';
                    $options .= "<option value='{$key}' {$selected}>{$label}</option>";
                }

                $badgeClass = order_status_badge_class($order->status);
                
                return "<div class='order-status-dropdown'>
                            <select class='form-control status-select-styled {$badgeClass}' data-id='{$order->id}'>
                                {$options}
                            </select>
                        </div>";
            })
            ->addColumn('child_data', function ($order) {
                $shipping = $order->shippingSnapshot ?: $order->shippingAddress;
                $billing = $order->billingSnapshot ?: $order->billingAddress;

                return [
                    'shipping' => [
                        'first_name' => $shipping->first_name ?? null,
                        'last_name' => $shipping->last_name ?? null,
                        'address_1' => $shipping->address_line ?? ($shipping->address_1 ?? null),
                        'address_2' => $shipping->address_2 ?? null,
                        'city' => $shipping->city_title ?? ($shipping->city ?? null),
                        'state_name' => $shipping->district_title ?? ($shipping->district ?? ($shipping->state_name ?? ($shipping->state ?? null))),
                        'phone' => $shipping->phone ?? ($order->customer_phone ?? null),
                    ],
                    'billing' => [
                        'first_name' => $billing->first_name ?? null,
                        'last_name' => $billing->last_name ?? null,
                        'company_name' => $billing->company_name ?? ($billing->invoice_title ?? null),
                        'tax_number' => $billing->tax_number ?? ($billing->invoice_tax_number ?? null),
                        'tax_office' => $billing->tax_office ?? ($billing->invoice_tax_office ?? null),
                        'billing_email' => $billing->billing_email ?? ($order->customer_email ?? null),
                        'address_1' => $billing->address_line ?? ($billing->address_1 ?? null),
                        'address_2' => $billing->address_2 ?? null,
                        'city' => $billing->city_title ?? ($billing->city ?? null),
                        'state_name' => $billing->district_title ?? ($billing->district ?? ($billing->state_name ?? ($billing->state ?? null))),
                        'phone' => $billing->phone ?? ($order->customer_phone ?? null),
                    ],
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'products' => $order->products->map(function($p) {
                        $variantSegments = [];

                        if ($p->hasAnyVariation()) {
                            foreach ($p->variations as $variation) {
                                $valueLabel = $variation->values->first()?->label;

                                if ($valueLabel) {
                                    $variantSegments[] = $variation->name . ': ' . $valueLabel;
                                }
                            }
                        }

                        return [
                            'name' => $p->product_name,
                            'variant' => !empty($variantSegments) ? implode(' · ', $variantSegments) : null,
                            'sku' => $p->product_sku,
                            'qty' => $p->qty,
                            'unit' => $p->unit_label,
                            'line_total' => $p->line_total->format(),
                            'image' => $p->product_variant?->base_image?->path 
                                ?? ($p->product?->base_image?->path 
                                ?? ($p->product_image_path ?? null)),
                        ];
                    })
                ];
            })
            ->editColumn('created', function ($order) {
                $date = optional($order->created_at)->format('d.m.Y');
                $time = optional($order->created_at)->format('H:i');
                return "<div class='created-cell'><div>" . e($date) . "</div><div>" . e($time) . "</div></div>";
            })
            ->addColumn('actions', function ($order) {
                $url = route('admin.orders.show', $order->id);
                $orderNo = e($order->displayOrderNumber());
                return "<a href='{$url}' class='action-edit' title='Düzenle ({$orderNo})' data-toggle='tooltip' aria-label='Edit'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                            <path d='M4 20H20' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M16.44 3.56006L20.44 7.56006' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                            <path d='M14.02 5.98999L6.91 13.1C6.52 13.49 6.15 14.25 6.07 14.81L5.64 17.83C5.45 19.08 6.42 20.04 7.67 19.86L10.69 19.43C11.25 19.35 12.01 18.98 12.41 18.59L19.52 11.48' stroke='#292D32' stroke-width='1.5' stroke-linecap='round'/>
                        </svg>
                    </a>";
            });
    }
}
