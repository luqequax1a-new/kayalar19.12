<?php

namespace Modules\Product\Admin;

use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Product\Entities\Product;

class ProductTable extends AdminTable
{
    /**
     * Raw columns that will not be escaped.
     *
     * @var array
     */
    protected array $rawColumns = ['sort_handle', 'price', 'in_stock', 'status', 'actions', 'name', 'brand', 'created_at'];

    /**
     * Create a new table instance.
     *
     * @param mixed $source
     * @return void
     */
    public function __construct($source = null)
    {
        $categoryId = request('category_id');
        $isSortMode = request('sort_mode') === '1';

        // Check if we have a valid category
        $hasValidCategory = $categoryId && $categoryId !== '0' && $categoryId !== 0;
        
        if ($hasValidCategory) {
            // Specific category sorting
            $joinCategoryId = (int) $categoryId;

            $source = $source->leftJoin('product_categories as pc_admin_sort', function($join) use ($joinCategoryId) {
                $join->on('products.id', '=', 'pc_admin_sort.product_id')
                     ->where('pc_admin_sort.category_id', '=', $joinCategoryId);
            })
            ->select('products.*')
            ->orderByRaw('pc_admin_sort.position IS NULL')
            ->orderBy('pc_admin_sort.position', 'asc');

            $source->resetOrders();
            $source->orderByRaw('pc_admin_sort.position IS NULL')
                   ->orderBy('pc_admin_sort.position', 'asc');
        } elseif ($isSortMode) {
            // Main page sorting (no category selected, but sort mode is active)
            // Use main_page_position column directly
            $source = $source->select('products.*')
                ->orderByRaw('main_page_position IS NULL')
                ->orderBy('main_page_position', 'asc');

            $source->resetOrders();
            $source->orderByRaw('main_page_position IS NULL')
                   ->orderBy('main_page_position', 'asc');
        }
        
        parent::__construct($source);
    }

    /**
     * Make table response for the resource.
     *
     * @return JsonResponse
     */
    public function make()
    {
        $table = $this->newTable();

        $table->orderColumn('in_stock', function ($query, $order) {
            $query->orderBy('stock_sort', $order)->orderBy('stock_qty_sort', $order);
        });

        return $table
            ->addColumn('sort_handle', function ($product) {
                return "<div class='sort-handle' style='cursor:move; text-align:center; padding:4px;'><i class='fa fa-bars' style='font-size:14px; color:#999;'></i></div>";
            })
            ->editColumn('thumbnail', function ($product) {
                $image = view('admin::partials.table.image', [
                    'file' => ($product->base_image && $product->base_image->id)
                        ? $product->base_image
                        : (($product->variant && $product->variant->base_image && $product->variant->base_image->id)
                            ? $product->variant->base_image
                            : $product->base_image),
                ])->render();
                return "<div class='image-cell'>{$image}</div>";
            })
            ->editColumn('price', function (Product $product) {
                if ($product->variants && $product->variants->count() > 0) {
                    $variants = $product->variants;

                    $originalMin = $variants->min(function ($v) { return $v->price->amount(); });
                    $originalMax = $variants->max(function ($v) { return $v->price->amount(); });

                    $promoMin = $variants->min(function ($v) { return $v->getSellingPrice()->amount(); });
                    $promoMax = $variants->max(function ($v) { return $v->getSellingPrice()->amount(); });

                    $originalMinFmt = \Modules\Support\Money::inDefaultCurrency($originalMin)->convertToCurrentCurrency()->format();
                    $originalMaxFmt = \Modules\Support\Money::inDefaultCurrency($originalMax)->convertToCurrentCurrency()->format();
                    $promoMinFmt = \Modules\Support\Money::inDefaultCurrency($promoMin)->convertToCurrentCurrency()->format();
                    $promoMaxFmt = \Modules\Support\Money::inDefaultCurrency($promoMax)->convertToCurrentCurrency()->format();

                    $originalRange = $originalMinFmt === $originalMaxFmt ? $originalMinFmt : "$originalMinFmt - $originalMaxFmt";
                    $promoRange = $promoMinFmt === $promoMaxFmt ? $promoMinFmt : "$promoMinFmt - $promoMaxFmt";

                    if ($originalRange !== $promoRange) {
                        return "<div class='price-cell' data-id='{$product->id}'><div class='price-top'><del class='text-red'>{$originalRange}</del></div><div class='price-bottom'>{$promoRange}</div></div>";
                    }

                    return "<div class='price-cell' data-id='{$product->id}'><div class='price-bottom'>{$originalRange}</div></div>";
                }

                // single/variant default
                $priceHtml = product_price_formatted($product->variant ?? $product, function ($price, $specialPrice) use ($product) {
                    if ($product->variant ? $product->variant->hasSpecialPrice() : $product->hasSpecialPrice()) {
                        return "<div class='price-cell' data-id='{$product->id}'><div class='price-top'><del class='text-red'>{$price}</del></div><div class='price-bottom'>{$specialPrice}</div></div>";
                    }
                    return "<div class='price-cell' data-id='{$product->id}'><div class='price-bottom'>{$price}</div></div>";
                });

                return $priceHtml;
            })
            ->addColumn('created_at', function (Product $product) {
                $createdAt = $product->created_at;

                if (!$createdAt) {
                    return '&mdash;';
                }

                $date = e($createdAt->translatedFormat('d F Y'));
                $time = e($createdAt->format('H:i'));

                return "<div class='created-at-cell'><div class='created-at-date'>{$date}</div><div class='created-at-time'>{$time}</div></div>";
            })
            ->addColumn('brand', function (Product $product) {
                $rawName = optional($product->brand)->name;
                $name = $rawName !== null && $rawName !== '' ? e($rawName) : '&mdash;';
                $id = (int) $product->id;
                $brandId = $product->brand_id ? (int) $product->brand_id : '';

                return "<span class='brand-cell' data-id='{$id}' data-brand-id='{$brandId}'>{$name}</span>";
            })
            ->addColumn('default_category', function (Product $product) {
                // Default category is always and only primaryCategory.
                // If current locale translation is empty, fall back to any available translation.
                $category = $product->primaryCategory;

                if (!$category) {
                    return '';
                }

                $name = $category->name;

                if ($name === null || $name === '') {
                    try {
                        $translation = $category->translations()
                            ->withoutGlobalScope('locale')
                            ->first();
                        if ($translation && isset($translation->name)) {
                            $name = $translation->name;
                        }
                    } catch (\Throwable $e) {
                        // ignore translation fallback errors
                    }
                }

                return e($name ?: '');
            })
            ->editColumn('in_stock', function (Product $product) {
                $isInStock = $product->isInStock();
                $badgeClass = $isInStock ? 'stock-badge' : 'stock-badge out-of-stock';

                if ($product->variants && $product->variants->count() > 0) {
                    $activeVariants = $product->variants->where('is_active', true);
                    $count = $activeVariants->count();
                    $noManage = !$product->manage_stock && ($product->variants->where('manage_stock', true)->count() === 0);

                    if ($noManage) {
                        $inner = "<div class='stock-total'>" . e('Stokta') . "</div>"
                            . "<div class='stock-count stock-variants'>" . e("{$count} varyant") . "</div>";
                        
                        return "<a href='#' class='inventory-click {$badgeClass}' data-id='{$product->id}'>{$inner}</a>";
                    }

                    $sumQty = (float) $activeVariants->sum(function ($v) { return (float) $v->qty; });
                    $suffix = $product->saleUnit ? trim($product->saleUnit->getDisplaySuffix()) : '';
                    $value = fmod($sumQty, 1) === 0.0
                        ? (string) (int) $sumQty
                        : rtrim(rtrim(number_format($sumQty, 2, '.', ''), '0'), '.');
                    $stockText = $suffix !== '' ? "$value $suffix" : "$value adet";

                    $inner = "<div class='stock-total'>" . e($stockText) . "</div>"
                        . "<div class='stock-count stock-variants'>" . e("{$count} varyant") . "</div>";

                    return "<a href='#' class='inventory-click {$badgeClass}' data-id='{$product->id}'>{$inner}</a>";
                }

                if (!$product->manage_stock) {
                    $inner = "<div class='stock-total'>" . e('Stokta') . "</div>";
                    return "<a href='#' class='inventory-click {$badgeClass}' data-id='{$product->id}'>{$inner}</a>";
                }

                $formatted = (string) $product->getFormattedStock();
                $trimmed = trim($formatted);

                if (preg_match('/^\d+(?:\.\d+)?$/', $trimmed)) {
                    $trimmed = $trimmed . ' adet';
                }

                $inner = "<div class='stock-total'>" . e($trimmed) . "</div>";
                return "<a href='#' class='inventory-click {$badgeClass}' data-id='{$product->id}'>{$inner}</a>";
            })
            ->editColumn('name', function (Product $product) {
                $url = route('admin.products.edit', $product->id);
                $id = (int) $product->id;
                $name = e($product->name);

                return "<div class='product-name-cell'><a href='{$url}' class='product-name-link' title='Düzenle'>{$name}</a><div class='product-id-under-name'>#{$id}</div></div>";
            })
            ->editColumn('status', function (Product $product) {
                $checked = $product->is_active ? 'checked' : '';

                return "<label class='switch'>
                    <input type='checkbox' class='product-status-switch' data-id='{$product->id}' {$checked} />
                    <span class='slider'></span>
                </label>";
            })
            ->addColumn('actions', function (Product $product) {
                $editUrl = route('admin.products.edit', $product->id);
                $viewUrl = route('products.show', $product->slug);
                $deleteId = $product->id;

                $duplicateForm = "<form method='POST' action='" . e(route('admin.products.duplicate', $product->id)) . "' style='display:inline'>" . csrf_field() . "
                        <button type='submit' class='action-duplicate' title='Copy' aria-label='Copy product' data-toggle='tooltip' style='background:none;border:none;padding:0;'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none'>
                                <rect x='9' y='9' width='10' height='10' rx='2' stroke='#292D32' stroke-width='1.5'/>
                                <rect x='5' y='5' width='10' height='10' rx='2' stroke='#292D32' stroke-width='1.5'/>
                            </svg>
                        </button>
                    </form>";

                return "<div class='actions-grid'>
                    <a href='{$editUrl}' class='btn-action-round' title='Düzenle' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z'></path></svg>
                    </a>
                    <a href='{$viewUrl}' target='_blank' class='btn-action-round' title='Görüntüle' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle></svg>
                    </a>
                    <form method='POST' action='" . e(route('admin.products.duplicate', $product->id)) . "' style='display:inline'>" . csrf_field() . "
                        <button type='submit' class='btn-action-round' title='Kopyala' data-toggle='tooltip' style='border:1px solid #e5e7eb;'>
                            <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><rect x='9' y='9' width='13' height='13' rx='2' ry='2'></rect><path d='M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1'></path></svg>
                        </button>
                    </form>
                    <a href='#' class='btn-action-round delete' data-id='{$deleteId}' title='Sil' data-toggle='tooltip'>
                        <svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'></polyline><path d='M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2'></path><line x1='10' y1='11' x2='10' y2='17'></line><line x1='14' y1='11' x2='14' y2='17'></line></svg>
                    </a>
                </div>";
            });
    }
}
