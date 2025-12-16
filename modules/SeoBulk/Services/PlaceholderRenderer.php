<?php

namespace Modules\SeoBulk\Services;

use Illuminate\Support\Str;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Category\Entities\Category;
use Modules\DynamicCategory\Entities\DynamicCategory;

class PlaceholderRenderer
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function renderProductTitle(Product $p): ?string
    {
        return $this->finalize($this->applyPlaceholders($this->config['templates']['title'],$this->productMap($p)));
    }

    public function renderProductDescription(Product $p): ?string
    {
        $raw = $this->applyPlaceholders($this->config['templates']['description'],$this->productMap($p));
        $raw = $raw ? Str::limit($raw,160,'') : null;
        return $this->finalize($raw);
    }

    public function renderProductAlt(Product $p, ?ProductVariant $v): ?string
    {
        $map = $this->productMap($p,$v);
        return $this->finalize($this->applyPlaceholders($this->config['templates']['alt'],$map));
    }

    public function renderCategoryTitle(Category $c): ?string
    {
        return $this->finalize($this->applyPlaceholders($this->config['templates']['title'],$this->categoryMap($c)));
    }

    public function renderCategoryDescription(Category $c): ?string
    {
        $raw = $this->applyPlaceholders($this->config['templates']['description'],$this->categoryMap($c));
        $raw = $raw ? Str::limit($raw,160,'') : null;
        return $this->finalize($raw);
    }

    public function renderDynamicCategoryTitle(DynamicCategory $c): ?string
    {
        return $this->finalize($this->applyPlaceholders($this->config['templates']['title'], $this->dynamicCategoryMap($c)));
    }

    public function renderDynamicCategoryDescription(DynamicCategory $c): ?string
    {
        $raw = $this->applyPlaceholders($this->config['templates']['description'], $this->dynamicCategoryMap($c));
        $raw = $raw ? Str::limit($raw, 160, '') : null;
        return $this->finalize($raw);
    }

    public function renderCategoryAlt(Category $c): ?string
    {
        return $this->finalize($this->applyPlaceholders($this->config['templates']['alt'],$this->categoryMap($c)));
    }

    private function productMap(Product $p, ?ProductVariant $v=null): array
    {
        $desc = $this->stripHtml($p->description ?? '');
        $summary = $p->short_description ?? '';
        if ($summary === '' && $desc !== '') {
            $summary = Str::limit($desc,150,'');
        }
        $brand = $p->brand->name ?? '';
        $variantAttrs = [];
        if ($v) {
            $variantAttrs = $this->extractVariantAttributes($v);
        }
        $shopName = setting('store_name', config('app.name'));

        return [
            '%shop.name%' => $shopName,
            '%separator%' => $this->config['templates']['separator'] ?? ' - ',
            '%product.name%' => $p->name ?? '',
            '%product.slug%' => $p->slug ?? '',
            '%product.summary%' => $summary ?? '',
            '%product.description%' => $desc ?? '',
            '%product.price%' => (string) ($p->price->amount() ?? ''),
            '%product.discount_price%' => (string) ($p->getSpecialPrice()->amount() ?? ''),
            '%product.sku%' => $p->sku ?? '',
            '%product.stock%' => $p->getFormattedStock() ?? '',
            '%category.name%' => optional($p->seoCategory())->name ?? '',
            '%category.slug%' => optional($p->seoCategory())->slug ?? '',
            '%brand%' => $brand ?? '',
            '%color%' => $variantAttrs['color'] ?? '',
            '%size%' => $variantAttrs['size'] ?? '',
            '%material%' => $variantAttrs['material'] ?? '',
        ];
    }

    private function categoryMap(Category $c): array
    {
        $desc = $c->getAttribute('meta_description') ?: '';
        if ($desc === '') {
            $desc = 'Affordable '.$c->name.' products with fast shipping';
        }

        $shopName = setting('store_name', config('app.name'));

        return [
            '%shop.name%' => $shopName,
            '%separator%' => $this->config['templates']['separator'] ?? ' - ',
            '%category.name%' => $c->name ?? '',
            '%category.slug%' => $c->slug ?? '',
            '%category.description%' => $desc ?? '',
            '%category.parent%' => optional($c->parent)->name ?? '',
        ];
    }

    private function dynamicCategoryMap(DynamicCategory $c): array
    {
        $desc = (string) ($c->meta_description ?? '');
        if ($desc === '') {
            $desc = (string) ($c->description ?? '');
        }
        $desc = $this->stripHtml($desc);

        $shopName = setting('store_name', config('app.name'));

        return [
            '%shop.name%' => $shopName,
            '%separator%' => $this->config['templates']['separator'] ?? ' - ',
            '%category.name%' => $c->name ?? '',
            '%category.slug%' => $c->slug ?? '',
            '%category.description%' => $desc ?? '',
            '%category.parent%' => '',
        ];
    }

    private function extractVariantAttributes(ProductVariant $v): array
    {
        return [];
    }

    private function applyPlaceholders(?string $tpl, array $map): ?string
    {
        if (!$tpl) return null;
        $out = $tpl;
        foreach ($map as $k=>$v) {
            $out = str_replace($k, $v ?? '', $out);
        }
        $out = preg_replace('/\s+\|\s+/',' '.$this->config['templates']['separator'].' ', $out);
        $out = preg_replace('/\s{2,}/',' ', $out);
        $out = trim($out);
        $out = preg_replace('/(\s*\|\s*){2,}/', $this->config['templates']['separator'], $out);
        $out = preg_replace('/\s*\|\s*$/','', $out);
        $out = preg_replace('/^\s*\|\s*/','', $out);
        $out = trim(preg_replace('/%[a-zA-Z0-9_.]+%/','', $out));
        return $out === '' ? null : $out;
    }

    private function stripHtml(?string $html): string
    {
        return trim(strip_tags((string) $html));
    }

    private function finalize(?string $text): ?string
    {
        return $text && trim($text) !== '' ? trim($text) : null;
    }
}
