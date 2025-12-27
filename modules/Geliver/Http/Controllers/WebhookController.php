<?php

namespace Modules\Geliver\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;

class WebhookController
{
    public function shipmentStatus(Request $request): JsonResponse
    {
        \Log::info('Geliver webhook hit', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'content_type' => $request->header('Content-Type'),
        ]);
        $secret = config('services.geliver.webhook_secret') ?: setting('geliver_webhook_secret');
        \Log::info('Geliver webhook secret check', [
            'configured_secret' => $secret ? 'SET' : 'EMPTY',
        ]);
        if ($secret) {
            $provided = $request->header('X-Geliver-Secret') ?: $request->query('secret');
            if (!$provided || $provided !== $secret) {
                return response()->json(['message' => 'unauthorized'], 401);
            }
        }

        $payload = $request->json()->all();
        if (empty($payload)) {
            $raw = $request->getContent();
            $decoded = null;
            try {
                $decoded = json_decode($raw, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            } catch (\Throwable $e) {
            }
            if (!is_array($decoded)) {
                $decoded = json_decode(utf8_encode($raw), true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            }
            if (!is_array($decoded) && stripos($request->header('Content-Type') ?? '', 'application/x-www-form-urlencoded') !== false) {
                $form = [];
                parse_str($raw, $form);
                if (is_array($form) && !empty($form)) { $decoded = $form; }
            }
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }
        \Log::info('GELIVER_PAYLOAD_DECODED', [
            'has_id' => isset($payload['id']) || isset($payload['shipmentID']) || isset($payload['data']['id']) || isset($payload['data']['shipmentID']) || isset($payload['shipment']['id']) || isset($payload['shipment']['shipmentID']),
            'has_status' => isset($payload['trackingSubStatusCode'])
                || isset($payload['trackingStatusCode'])
                || isset($payload['trackingStatus']['trackingSubStatusCode'])
                || isset($payload['trackingStatus']['trackingStatusCode'])
                || isset($payload['data']['trackingStatus']['trackingSubStatusCode'])
                || isset($payload['data']['trackingStatus']['trackingStatusCode'])
                || isset($payload['shipment']['trackingStatus']['trackingSubStatusCode'])
                || isset($payload['shipment']['trackingStatus']['trackingStatusCode'])
                || isset($payload['status'])
                || isset($payload['statusCode'])
                || isset($payload['data']['status'])
                || isset($payload['data']['statusCode'])
                || isset($payload['shipment']['status'])
                || isset($payload['shipment']['statusCode']),
        ]);
        $shipmentId = $payload['id']
            ?? ($payload['shipmentID'] ?? null)
            ?? ($payload['data']['id'] ?? null)
            ?? ($payload['data']['shipmentID'] ?? null)
            ?? ($payload['shipment']['id'] ?? null)
            ?? ($payload['shipment']['shipmentID'] ?? null);
        $payloadStatus = null;
        $statusSource = null;
        if (isset($payload['trackingSubStatusCode'])) {
            $payloadStatus = $payload['trackingSubStatusCode'];
            $statusSource = 'trackingSubStatusCode';
        } elseif (null !== data_get($payload, 'trackingStatus.trackingSubStatusCode')) {
            $payloadStatus = data_get($payload, 'trackingStatus.trackingSubStatusCode');
            $statusSource = 'trackingStatus.trackingSubStatusCode';
        } elseif (null !== data_get($payload, 'data.trackingStatus.trackingSubStatusCode')) {
            $payloadStatus = data_get($payload, 'data.trackingStatus.trackingSubStatusCode');
            $statusSource = 'data.trackingStatus.trackingSubStatusCode';
        } elseif (null !== data_get($payload, 'shipment.trackingStatus.trackingSubStatusCode')) {
            $payloadStatus = data_get($payload, 'shipment.trackingStatus.trackingSubStatusCode');
            $statusSource = 'shipment.trackingStatus.trackingSubStatusCode';
        } elseif (isset($payload['trackingStatusCode'])) {
            $payloadStatus = $payload['trackingStatusCode'];
            $statusSource = 'trackingStatusCode';
        } elseif (null !== data_get($payload, 'trackingStatus.trackingStatusCode')) {
            $payloadStatus = data_get($payload, 'trackingStatus.trackingStatusCode');
            $statusSource = 'trackingStatus.trackingStatusCode';
        } elseif (null !== data_get($payload, 'data.trackingStatus.trackingStatusCode')) {
            $payloadStatus = data_get($payload, 'data.trackingStatus.trackingStatusCode');
            $statusSource = 'data.trackingStatus.trackingStatusCode';
        } elseif (null !== data_get($payload, 'shipment.trackingStatus.trackingStatusCode')) {
            $payloadStatus = data_get($payload, 'shipment.trackingStatus.trackingStatusCode');
            $statusSource = 'shipment.trackingStatus.trackingStatusCode';
        } elseif (isset($payload['status'])) {
            $payloadStatus = $payload['status'];
            $statusSource = 'status';
        } elseif (isset($payload['statusCode'])) {
            $payloadStatus = $payload['statusCode'];
            $statusSource = 'statusCode';
        } elseif (null !== data_get($payload, 'data.status')) {
            $payloadStatus = data_get($payload, 'data.status');
            $statusSource = 'data.status';
        } elseif (null !== data_get($payload, 'data.statusCode')) {
            $payloadStatus = data_get($payload, 'data.statusCode');
            $statusSource = 'data.statusCode';
        } elseif (null !== data_get($payload, 'shipment.status')) {
            $payloadStatus = data_get($payload, 'shipment.status');
            $statusSource = 'shipment.status';
        } elseif (null !== data_get($payload, 'shipment.statusCode')) {
            $payloadStatus = data_get($payload, 'shipment.statusCode');
            $statusSource = 'shipment.statusCode';
        }
        if (!$shipmentId || !$payloadStatus) {
            return response()->json(['message' => 'invalid payload'], 400);
        }

        $order = Order::where('geliver_shipment_id', $shipmentId)->first();
        if (!$order) {
            $orderNumber = $payload['order']['orderNumber']
                ?? ($payload['data']['order']['orderNumber'] ?? null)
                ?? ($payload['orderNumber'] ?? null)
                ?? ($payload['shipment']['order']['orderNumber'] ?? null);
            if ($orderNumber) {
                $order = Order::where('id', $orderNumber)->first();
            }
        }

        if ($order) {
            \Log::info('Geliver webhook order found', [
                'order_id' => $order->id,
                'shipment_id' => $shipmentId,
                'status' => $payloadStatus,
                'status_source' => $statusSource,
            ]);
        }
        \Log::info('GELIVER_STATUS_UPDATE_CANDIDATE', [
            'status' => $payloadStatus,
            'shipment_id' => $shipmentId,
            'order_found' => (bool) $order,
        ]);

        if (!$order) {
            Log::warning('Geliver webhook: order not found', ['shipment_id' => $shipmentId]);
            return response()->json(['message' => 'order not found'], 200);
        }

        $trackingNumber = data_get($payload, 'tracking_number')
            ?? data_get($payload, 'trackingNo')
            ?? data_get($payload, 'trackingNumber')
            ?? data_get($payload, 'shipment.tracking_number')
            ?? data_get($payload, 'shipment.trackingNo')
            ?? data_get($payload, 'data.tracking_number')
            ?? data_get($payload, 'data.trackingNo')
            ?? data_get($payload, 'awb')
            ?? data_get($payload, 'waybillNo');
        $carrierName = data_get($payload, 'carrier')
            ?? data_get($payload, 'carrierName')
            ?? data_get($payload, 'carrier.name')
            ?? data_get($payload, 'providerName')
            ?? data_get($payload, 'providerCode')
            ?? data_get($payload, 'trackingStatus.providerName')
            ?? data_get($payload, 'trackingStatus.providerCode')
            ?? data_get($payload, 'data.providerName')
            ?? data_get($payload, 'data.providerCode')
            ?? data_get($payload, 'data.trackingStatus.providerName')
            ?? data_get($payload, 'data.trackingStatus.providerCode')
            ?? data_get($payload, 'shipment.providerName')
            ?? data_get($payload, 'shipment.providerCode')
            ?? data_get($payload, 'shipment.trackingStatus.providerName')
            ?? data_get($payload, 'shipment.trackingStatus.providerCode')
            ?? data_get($payload, 'shipment.carrier')
            ?? data_get($payload, 'shipment.carrierName')
            ?? data_get($payload, 'shipment.carrier.name')
            ?? data_get($payload, 'data.carrier')
            ?? data_get($payload, 'data.carrierName')
            ?? data_get($payload, 'data.carrier.name');
        $trackingUrl = data_get($payload, 'tracking_url')
            ?? data_get($payload, 'trackingUrl')
            ?? data_get($payload, 'shipment.tracking_url')
            ?? data_get($payload, 'shipment.trackingUrl')
            ?? data_get($payload, 'data.tracking_url')
            ?? data_get($payload, 'data.trackingUrl')
            ?? data_get($payload, 'tracking.link')
            ?? data_get($payload, 'shipment.tracking.link')
            ?? data_get($payload, 'data.tracking.link');

        if ((!$trackingNumber || !$carrierName) && $shipmentId) {
            try {
                $svc = app(\Modules\Geliver\Services\GeliverService::class);
                $remote = $svc->fetchShipmentById($shipmentId);
                if (!$trackingNumber) { $trackingNumber = $svc->extractTrackingNumber($remote); }
                if (!$carrierName) { $carrierName = $svc->extractCarrierName($remote); }
                if (!$trackingUrl) { $trackingUrl = $svc->extractTrackingUrl($remote); }
            } catch (\Throwable $e) {
            }
        }

        $updatePayload = [
            'geliver_last_status' => $payloadStatus,
            'geliver_last_status_at' => now(),
        ];
        if ($trackingNumber) {
            $updatePayload['shipping_tracking_number'] = (string) $trackingNumber;
        }
        if ($carrierName) {
            $updatePayload['shipping_carrier_name'] = is_array($carrierName) ? ($carrierName['name'] ?? json_encode($carrierName)) : (string) $carrierName;
        }
        if ($trackingUrl && filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
            $updatePayload['shipping_tracking_url'] = (string) $trackingUrl;
            $updatePayload['tracking_reference'] = (string) $trackingUrl;
        }

        if (!isset($updatePayload['tracking_reference'])) {
            $existingRef = isset($order->tracking_reference) ? trim((string) $order->tracking_reference) : '';
            if ($existingRef === '') {
                if ($trackingNumber) {
                    $updatePayload['tracking_reference'] = (string) $trackingNumber;
                }
            }
        }

        $map = config('geliver.status_map');
        $finals = config('geliver.final_statuses');
        $statusKey = is_string($payloadStatus) ? $payloadStatus : (string) $payloadStatus;

        $statusKeyNorm = $this->normalizeStatusKey($statusKey);

        $packageAcceptedNorms = [
            'packageaccepted',
            'kabuledildi',
        ];
        $onTheWayNorms = [
            'deliveryscheduled',
            'yolda',
            'ontheway',
            'intheway',
            'intransit',
        ];
        $outForDeliveryNorms = [
            'outfordelivery',
            'dagitimda',
            'dagıtımda',
        ];
        $deliveredNorms = [
            'delivered',
            'teslimedildi',
        ];
        $inTransitNorms = [
            'readytoship',
            'packageaccepted',
            'shipped',
        ];

        $newStatus = null;

        if (in_array($statusKeyNorm, $deliveredNorms, true)) {
            $shouldComplete = false;
            $minAgeSeconds = (int) (config('geliver.delivered_min_age_seconds') ?? 0);
            $remoteOk = false;
            $remoteNorm = '';
            $prevStatusNorm = $this->normalizeStatusKey((string) ($order->geliver_last_status ?? ''));
            $prevAt = $order->geliver_last_status_at ? Carbon::parse($order->geliver_last_status_at) : null;
            $prevAge = $prevAt ? $prevAt->diffInSeconds(now()) : null;
            if ($shipmentId) {
                try {
                    $svc = app(\Modules\Geliver\Services\GeliverService::class);
                    $remote = $svc->fetchShipmentById((string) $shipmentId);
                    if (is_array($remote)) {
                        $remoteStatus = $this->extractStatusKey($remote);
                        $remoteNorm = $remoteStatus ? $this->normalizeStatusKey($remoteStatus) : '';
                        $remoteOk = $remoteNorm !== '';
                        if ($remoteNorm !== '') {
                            if (in_array($remoteNorm, $deliveredNorms, true)) {
                                if ($minAgeSeconds <= 0) {
                                    $shouldComplete = true;
                                } elseif (
                                    in_array($prevStatusNorm, $deliveredNorms, true)
                                    && $prevAge !== null
                                    && $prevAge >= $minAgeSeconds
                                ) {
                                    $shouldComplete = true;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
            if (!$remoteOk) {
                if ($minAgeSeconds <= 0) {
                    $shouldComplete = true;
                } elseif (
                    in_array($prevStatusNorm, $deliveredNorms, true)
                    && $prevAge !== null
                    && $prevAge >= $minAgeSeconds
                ) {
                    $shouldComplete = true;
                }
            }

            Log::info('Geliver delivered decision', [
                'shipment_id' => $shipmentId,
                'status_raw' => $payloadStatus,
                'remote_ok' => $remoteOk,
                'remote_status_norm' => $remoteNorm,
                'prev_status_norm' => $prevStatusNorm,
                'age_since_last_status_at' => $prevAge,
                'min_age_seconds' => $minAgeSeconds,
                'order_status_before' => $order->status,
                'should_complete' => $shouldComplete,
            ]);

            $newStatus = $shouldComplete ? \Modules\Order\Entities\Order::COMPLETED : \Modules\Order\Entities\Order::SHIPPED;
        } elseif (in_array($statusKeyNorm, $outForDeliveryNorms, true)) {
            $newStatus = \Modules\Order\Entities\Order::OUT_FOR_DELIVERY;
        } elseif (in_array($statusKeyNorm, $onTheWayNorms, true)) {
            $newStatus = \Modules\Order\Entities\Order::ON_THE_WAY;
        } elseif (in_array($statusKeyNorm, $packageAcceptedNorms, true)) {
            $newStatus = \Modules\Order\Entities\Order::SHIPPED;
        } elseif (in_array($statusKeyNorm, $inTransitNorms, true)) {
            $newStatus = \Modules\Order\Entities\Order::SHIPPED;
        }

        if ($newStatus === null) {
            $newStatus = $map[$statusKey] ?? null;
        }

        if ($newStatus === null) {
            $statusKeyLower = mb_strtolower($statusKey, 'UTF-8');
            $mapLower = [];
            foreach ($map as $k => $v) {
                $mapLower[mb_strtolower((string) $k, 'UTF-8')] = $v;
            }
            $newStatus = $mapLower[$statusKeyLower] ?? null;
        }

        if ($newStatus === null) {
            $mapNorm = [];
            foreach ($map as $k => $v) {
                $mapNorm[$this->normalizeStatusKey((string) $k)] = $v;
            }
            $newStatus = $mapNorm[$statusKeyNorm] ?? null;
        }
        Log::info('Geliver webhook status resolved', [
            'shipment_id' => $shipmentId,
            'status_raw' => $payloadStatus,
            'status_norm' => $statusKeyNorm,
            'new_status' => $newStatus,
            'order_id' => $order->id,
            'order_status_before' => $order->status,
        ]);
        // tracking bilgilerini önce yaz ki email eventinde mevcut olsun
        $order->update($updatePayload);

        // no fallback: if status not mapped, we only update tracking fields without transition
        if ($newStatus === null) {
            Log::info('Geliver webhook: status not mapped', ['status' => $payloadStatus, 'shipment_id' => $shipmentId]);
            return response()->json(['message' => 'ignored'], 200);
        }

        if (in_array($order->status, $finals, true) && !in_array($newStatus, $finals, true)) {
            if ($order->status === \Modules\Order\Entities\Order::COMPLETED && $newStatus === \Modules\Order\Entities\Order::SHIPPED && in_array($statusKeyNorm, $inTransitNorms, true)) {
                // allow correcting an order that was mistakenly marked completed
            } else {
                return response()->json(['message' => 'final status preserved'], 200);
            }
        }

        $request->attributes->set('order_status_change_source', 'geliver_webhook');
        $request->attributes->set('order_status_change_context', [
            'shipment_id' => $shipmentId,
            'status_raw' => $payloadStatus,
            'status_norm' => $statusKeyNorm,
        ]);

        $order->transitionTo($newStatus);
        \Log::info('Geliver webhook status transitioned', [
            'order_id' => $order->id,
            'new_status' => $newStatus,
            'tracking' => $updatePayload['shipping_tracking_number'] ?? null,
            'carrier' => $updatePayload['shipping_carrier_name'] ?? null,
        ]);
        \Log::info('GELIVER_STATUS_UPDATED', [
            'order_db_id' => $order->id,
            'new_status' => $newStatus,
            'shipment_id' => $shipmentId,
        ]);

        return response()->json(['message' => 'ok'], 200);
    }

    private function normalizeStatusKey(string $value): string
    {
        $v = trim($value);
        $v = mb_strtolower($v, 'UTF-8');
        $v = preg_replace('/[^\p{L}\p{N}]+/u', '', $v);
        return $v ?? '';
    }

    private function extractStatusKey(array $payload): ?string
    {
        $v = data_get($payload, 'trackingSubStatusCode')
            ?? data_get($payload, 'trackingStatus.trackingSubStatusCode')
            ?? data_get($payload, 'trackingStatus.trackingStatusCode')
            ?? data_get($payload, 'trackingStatusCode')
            ?? data_get($payload, 'status')
            ?? data_get($payload, 'statusCode')
            ?? data_get($payload, 'shipment.status')
            ?? data_get($payload, 'shipment.statusCode')
            ?? data_get($payload, 'data.status')
            ?? data_get($payload, 'data.statusCode');
        return $v !== null ? (string) $v : null;
    }
}
