<?php

namespace Modules\Admin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Support\Money;
use Modules\Order\Entities\Order;

class CartActivityController
{
    public function index(Request $request)
    {
        $payload = Cache::remember('admin:dashboard:cart_activity:v2', now()->addSeconds(20), function () {
            $base = DB::table('carts')
                ->where('id', 'like', '%_cart_items');

            $now = now();

            $computeWindow = function ($since) use ($base) {
                $count = (clone $base)
                    ->where('updated_at', '>=', $since)
                    ->count();

                $sampleRows = (clone $base)
                    ->where('updated_at', '>=', $since)
                    ->orderByDesc('updated_at')
                    ->limit(50)
                    ->get(['data']);

                $total = 0.0;
                $itemsQty = 0.0;

                foreach ($sampleRows as $row) {
                    $parsed = $this->parseCartPayload($row->data);
                    $total += $parsed['total'];
                    $itemsQty += $parsed['items_qty'];
                }

                return [
                    'count' => (int) $count,
                    'total_amount' => (float) $total,
                    'total_amount_formatted' => Money::inDefaultCurrency((float) $total)->format(),
                    'items_qty' => (float) $itemsQty,
                    'items_qty_formatted' => rtrim(rtrim(number_format((float) $itemsQty, 2, '.', ''), '0'), '.'),
                    'sampled' => true,
                ];
            };

            $last30 = $computeWindow($now->copy()->subMinutes(30));
            $last60 = $computeWindow($now->copy()->subHour());
            $today = $computeWindow($now->copy()->startOfDay());

            $computeVisits = function ($since) {
                return (int) DB::table('page_views')
                    ->where('created_at', '>=', $since)
                    ->distinct('fingerprint')
                    ->count('fingerprint');
            };

            $computeOrders = function ($since) {
                $rows = Order::query()
                    ->withoutCanceledOrders()
                    ->where('created_at', '>=', $since)
                    ->selectRaw('COUNT(*) as orders')
                    ->selectRaw('SUM(total) as revenue')
                    ->first();

                $orders = (int) ($rows->orders ?? 0);
                $revenue = (float) ($rows->revenue ?? 0);

                return [
                    'orders' => $orders,
                    'revenue' => $revenue,
                    'revenue_formatted' => Money::inDefaultCurrency($revenue)->format(),
                ];
            };

            $visitsLast30 = $computeVisits($now->copy()->subMinutes(30));
            $visitsLast60 = $computeVisits($now->copy()->subHour());
            $visitsToday = $computeVisits($now->copy()->startOfDay());

            $ordersLast30 = $computeOrders($now->copy()->subMinutes(30));
            $ordersLast60 = $computeOrders($now->copy()->subHour());
            $ordersToday = $computeOrders($now->copy()->startOfDay());

            $recentRows = (clone $base)
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'data', 'updated_at']);

            $recentCarts = $recentRows
                ->map(function ($row) {
                    $parsed = $this->parseCartPayload($row->data);

                    return [
                        'id' => (string) $row->id,
                        'updated_at' => (string) $row->updated_at,
                        'items' => $parsed['items_preview'],
                        'items_qty' => (float) $parsed['items_qty'],
                        'total_amount' => (float) $parsed['total'],
                        'total_amount_formatted' => Money::inDefaultCurrency((float) $parsed['total'])->format(),
                    ];
                })
                ->values();

            return [
                'ranges' => [
                    'last_30_min' => $last30,
                    'last_60_min' => $last60,
                    'today' => $today,
                ],
                'visits' => [
                    'last_30_min' => $visitsLast30,
                    'last_60_min' => $visitsLast60,
                    'today' => $visitsToday,
                ],
                'orders' => [
                    'last_30_min' => $ordersLast30,
                    'last_60_min' => $ordersLast60,
                    'today' => $ordersToday,
                ],
                'recent_carts' => $recentCarts,
            ];
        });

        return response()->json($payload);
    }

    private function parseCartPayload($serialized): array
    {
        $total = 0.0;
        $itemsQty = 0.0;
        $preview = [];

        try {
            $decoded = null;

            if (is_string($serialized)) {
                $decoded = @unserialize($serialized);

                if ($decoded === false && trim($serialized) !== 'b:0;') {
                    try {
                        $maybeJson = json_decode($serialized, true);
                        if (is_array($maybeJson)) {
                            $decoded = $maybeJson;
                        }
                    } catch (\Throwable $e) {
                    }
                }
            } else {
                $decoded = $serialized;
            }

            if ($decoded instanceof \Darryldecode\Cart\CartCollection) {
                $decoded = $decoded->all();
            } elseif ($decoded instanceof \Illuminate\Support\Collection) {
                $decoded = $decoded->all();
            } elseif ($decoded instanceof \Traversable) {
                $decoded = iterator_to_array($decoded);
            } elseif (is_object($decoded) && method_exists($decoded, 'toArray')) {
                try {
                    $decoded = $decoded->toArray();
                } catch (\Throwable $e) {
                }
            }

            if (!is_array($decoded)) {
                return [
                    'total' => 0.0,
                    'items_qty' => 0.0,
                    'items_preview' => [],
                ];
            }

            foreach (array_slice($decoded, 0, 10) as $item) {
                $name = null;
                $qty = null;
                $price = null;
                $fallbackItem = null;

                if (is_array($item)) {
                    $name = $item['name'] ?? null;
                    $qty = $item['quantity'] ?? ($item['qty'] ?? null);
                    $price = $item['price'] ?? null;

                    if (!$name && isset($item['attributes']) && is_array($item['attributes'])) {
                        $name = $item['attributes']['product']['name'] ?? null;
                    }

                    if (isset($item['attributes']) && is_array($item['attributes'])) {
                        $fallbackItem = $item['attributes']['item'] ?? ($item['attributes']['variant'] ?? null);
                    }
                } elseif (is_object($item)) {
                    $name = $item->name ?? null;
                    $qty = $item->quantity ?? ($item->qty ?? null);
                    $price = $item->price ?? null;

                    if (isset($item->attributes) && is_array($item->attributes)) {
                        $fallbackItem = $item->attributes['item'] ?? ($item->attributes['variant'] ?? null);
                    }
                }

                $q = $qty === null ? 1.0 : (float) $qty;

                $p = 0.0;
                if ($price instanceof Money) {
                    $p = (float) $price->amount();
                } elseif (is_array($price) && isset($price['amount']) && is_numeric($price['amount'])) {
                    $p = (float) $price['amount'];
                } elseif ($price !== null && is_numeric($price)) {
                    $p = (float) $price;
                }

                if ($p <= 0 && $fallbackItem) {
                    $candidate = null;

                    if ($fallbackItem instanceof Money) {
                        $candidate = $fallbackItem;
                    } elseif (is_object($fallbackItem)) {
                        $candidate = $fallbackItem->selling_price
                            ?? $fallbackItem->price
                            ?? $fallbackItem->special_price
                            ?? null;
                    } elseif (is_array($fallbackItem)) {
                        $candidate = $fallbackItem['selling_price']
                            ?? $fallbackItem['price']
                            ?? $fallbackItem['special_price']
                            ?? null;
                    }

                    if ($candidate instanceof Money) {
                        $p = (float) $candidate->amount();
                    } elseif (is_array($candidate) && isset($candidate['amount']) && is_numeric($candidate['amount'])) {
                        $p = (float) $candidate['amount'];
                    } elseif ($candidate !== null && is_numeric($candidate)) {
                        $p = (float) $candidate;
                    }
                }

                $itemsQty += $q;
                $total += $p * $q;

                if ($name) {
                    $preview[] = [
                        'name' => (string) $name,
                        'qty' => $q,
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        return [
            'total' => (float) $total,
            'items_qty' => (float) $itemsQty,
            'items_preview' => array_slice($preview, 0, 3),
        ];
    }
}
