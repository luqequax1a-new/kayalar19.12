<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\StockNotifyRequest;

class StockNotifyController
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'email' => ['nullable', 'email'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first() ?: 'Geçersiz istek.',
                ], 422);
            }

            return back()->with('error', $validator->errors()->first() ?: 'Geçersiz istek.');
        }

        $validated = $validator->validated();

        $email = (string) ($validated['email'] ?? '');

        if ($email === '' && auth()->check()) {
            $email = (string) auth()->user()->email;
        }

        if ($email === '') {
            $message = 'E-posta adresi gerekli.';

            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $product = Product::withoutGlobalScope('active')
            ->with('variants')
            ->findOrFail((int) $validated['product_id']);

        $variantId = $validated['variant_id'] ?? null;

        if (! is_null($variantId)) {
            $variant = $product->variants
                ->where('id', (int) $variantId)
                ->first();

            if (! $variant) {
                $message = 'Geçersiz varyant.';

                if ($request->expectsJson()) {
                    return response()->json(['status' => 'error', 'message' => $message], 422);
                }

                return back()->with('error', $message);
            }

            if ($variant->isInStock()) {
                $message = 'Ürün şu an stokta.';

                if ($request->expectsJson()) {
                    return response()->json(['status' => 'ok', 'message' => $message]);
                }

                return back()->with('success', $message);
            }
        } else {
            if ($product->isInStock()) {
                $message = 'Ürün şu an stokta.';

                if ($request->expectsJson()) {
                    return response()->json(['status' => 'ok', 'message' => $message]);
                }

                return back()->with('success', $message);
            }
        }

        $existingQuery = StockNotifyRequest::query()
            ->where('product_id', $product->id)
            ->where('email', $email)
            ->whereNull('sent_at');

        if (is_null($variantId)) {
            $existingQuery->whereNull('variant_id');
        } else {
            $existingQuery->where('variant_id', (int) $variantId);
        }

        if ($existingQuery->exists()) {
            $message = 'Talebiniz Alındı. Ürün tekrar stok açıldığında size haber vereceğiz.';

            if ($request->expectsJson()) {
                return response()->json(['status' => 'ok', 'message' => $message]);
            }

            return back()->with('success', $message);
        }

        StockNotifyRequest::create([
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'email' => $email,
            'sent_at' => null,
        ]);

        $message = 'Talebiniz alındı. Ürün tekrar stok açıldığında size haber vereceğiz.';

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
