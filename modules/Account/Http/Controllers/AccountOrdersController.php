<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Response;
use Modules\Geliver\Services\GeliverService;

class AccountOrdersController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->latest()
            ->paginate(20);

        return view('storefront::public.account.orders.index', compact('orders'));
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $order = auth()->user()
            ->orders()
            ->with(['products', 'coupon', 'taxes'])
            ->where('id', $id)
            ->firstOrFail();

        // Backfill shipping carrier / tracking fields for older orders
        // when Geliver data exists but webhook did not populate these fields.
        try {
            $needsCarrier = !isset($order->shipping_carrier_name) || trim((string) $order->shipping_carrier_name) === '';
            $needsTrackingNo = !isset($order->shipping_tracking_number) || trim((string) $order->shipping_tracking_number) === '';
            $needsTrackingUrl = !isset($order->shipping_tracking_url) || trim((string) $order->shipping_tracking_url) === '';

            if (($needsCarrier || $needsTrackingNo || $needsTrackingUrl) && !empty($order->geliver_shipment_id)) {
                $svc = app(GeliverService::class);
                $remote = $svc->fetchShipmentById((string) $order->geliver_shipment_id);

                if (is_array($remote)) {
                    $upd = [];
                    if ($needsCarrier) {
                        $carrier = $svc->extractCarrierName($remote);
                        if ($carrier) {
                            $upd['shipping_carrier_name'] = $carrier;
                        }
                    }
                    if ($needsTrackingNo) {
                        $trkNo = $svc->extractTrackingNumber($remote);
                        if ($trkNo) {
                            $upd['shipping_tracking_number'] = $trkNo;
                        }
                    }
                    if ($needsTrackingUrl) {
                        $trkUrl = $svc->extractTrackingUrl($remote);
                        if ($trkUrl && filter_var($trkUrl, FILTER_VALIDATE_URL)) {
                            $upd['shipping_tracking_url'] = $trkUrl;
                        }
                    }

                    if (!empty($upd)) {
                        $order->update($upd);
                        foreach ($upd as $k => $v) {
                            $order->{$k} = $v;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        return view('storefront::public.account.orders.show', compact('order'));
    }
}
