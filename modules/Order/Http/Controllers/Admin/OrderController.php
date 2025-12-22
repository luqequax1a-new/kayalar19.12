<?php

namespace Modules\Order\Http\Controllers\Admin;

use Modules\Order\Entities\Order;
use Modules\Admin\Traits\HasCrudActions;

class OrderController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['products', 'coupon', 'taxes', 'billingSnapshot', 'shippingSnapshot'];

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'order::orders.order';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'order::admin.orders';

    public function update($id)
    {
        $entity = $this->getEntity($id);
        $this->disableSearchSyncing();
        $data = $this->getRequest('update')->except(array_keys(request()->query()));
        $entity->update($data);
        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });
        $this->searchable($entity);

        $carrier = isset($data['shipping_carrier_name']) ? trim((string) $data['shipping_carrier_name']) : null;
        $trackingNo = isset($data['shipping_tracking_number']) ? trim((string) $data['shipping_tracking_number']) : null;
        $trackingUrl = isset($data['shipping_tracking_url']) ? trim((string) $data['shipping_tracking_url']) : null;
        $trackingRef = isset($data['tracking_reference']) ? trim((string) $data['tracking_reference']) : null;

        $upd = [];

        if ($carrier !== null) {
            $upd['shipping_carrier_name'] = $carrier;
        }
        if ($trackingNo !== null) {
            $upd['shipping_tracking_number'] = $trackingNo;
        }
        if ($trackingUrl !== null) {
            $upd['shipping_tracking_url'] = $trackingUrl;
        }

        if ($trackingRef !== null && $trackingRef !== '') {
            $refUrl = null;
            $refNo = null;

            if (filter_var($trackingRef, FILTER_VALIDATE_URL)) {
                $refUrl = $trackingRef;
                $parts = parse_url($trackingRef);
                $q = $parts['query'] ?? '';
                if ($q !== '') {
                    parse_str($q, $qp);
                    if (isset($qp['code']) && is_string($qp['code']) && $qp['code'] !== '') {
                        $refNo = $qp['code'];
                    }
                }
            } else {
                $refNo = $trackingRef;
            }

            // keep original reference
            $upd['tracking_reference'] = $trackingRef;

            if ($refUrl && ($trackingUrl === null || $trackingUrl === '')) {
                $upd['shipping_tracking_url'] = $refUrl;
            }
            if ($refNo && ($trackingNo === null || $trackingNo === '')) {
                $upd['shipping_tracking_number'] = $refNo;
            }
        }

        // if user provided structured tracking URL/number but did not set tracking_reference, fill it
        if ((!isset($upd['tracking_reference']) || $upd['tracking_reference'] === '') && $trackingRef !== null) {
            // tracking_reference explicitly provided as empty -> do not auto fill
        } elseif (!isset($upd['tracking_reference']) || $upd['tracking_reference'] === '') {
            if (isset($upd['shipping_tracking_url']) && is_string($upd['shipping_tracking_url']) && $upd['shipping_tracking_url'] !== '' && filter_var($upd['shipping_tracking_url'], FILTER_VALIDATE_URL)) {
                $upd['tracking_reference'] = $upd['shipping_tracking_url'];
            } elseif (isset($upd['shipping_tracking_number']) && is_string($upd['shipping_tracking_number']) && $upd['shipping_tracking_number'] !== '') {
                $upd['tracking_reference'] = $upd['shipping_tracking_number'];
            }
        }

        if (!empty($upd)) {
            $entity->update($upd);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]),
            ], 200);
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }
}
