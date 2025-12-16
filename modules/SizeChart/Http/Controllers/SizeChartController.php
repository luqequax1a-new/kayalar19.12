<?php

namespace Modules\SizeChart\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Services\SizeChartResolver;

class SizeChartController
{
    public function __construct(private readonly SizeChartResolver $resolver)
    {
    }

    public function showForProduct(int $productId): JsonResponse
    {
        $product = Product::query()->findOrFail($productId);

        $charts = $this->resolver->resolveAllForProduct($product);

        if ($charts->isEmpty()) {
            return response()->json(['exists' => false], 404);
        }

        $first = $charts->first();

        $payloadCharts = $charts->map(function (SizeChart $chart) {
            return [
                'id' => $chart->id,
                'title' => $chart->title,
                'type' => $chart->type,
                'html' => $chart->type === SizeChart::TYPE_HTML ? $this->sanitizeHtml((string) $chart->content_html) : null,
                'image_url' => $chart->type === SizeChart::TYPE_IMAGE ? $chart->image->path : null,
            ];
        })->values();

        return response()->json([
            'exists' => true,
            'charts' => $payloadCharts,

            // Backward compatibility
            'title' => $first->title,
            'type' => $first->type,
            'html' => $first->type === SizeChart::TYPE_HTML ? $this->sanitizeHtml((string) $first->content_html) : null,
            'image_url' => $first->type === SizeChart::TYPE_IMAGE ? $first->image->path : null,
        ]);
    }

    private function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><br><b><strong><i><em><u><ul><ol><li><table><thead><tbody><tr><th><td><span><div><h1><h2><h3><h4><h5><h6><a><img>';

        $clean = strip_tags($html, $allowedTags);

        $clean = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? '';

        $clean = preg_replace_callback('/\s(href|src)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function ($m) {
            $attr = strtolower($m[1]);
            $raw = trim($m[2]);
            $value = trim($raw, "\"' ");

            if (preg_match('/^\s*javascript:/i', $value)) {
                return '';
            }

            return " {$attr}=\"" . e($value) . "\"";
        }, $clean) ?? '';

        return $clean;
    }
}
