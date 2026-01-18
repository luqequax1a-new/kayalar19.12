<?php

namespace Modules\Cart\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cart\Entities\CartUpsellRule;
use Modules\Product\Entities\Product;

class CartUpsellRuleController extends Controller
{
    public function index()
    {
        $rules = CartUpsellRule::with(['mainProduct', 'upsellProduct', 'preselectedVariant', 'mainCategory'])
            ->orderByDesc('sort_order')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // Calculate metrics
        $metrics = [
            'total_orders' => CartUpsellRule::sum('times_purchased'),
            'total_added_to_cart' => CartUpsellRule::sum('times_added_to_cart'),
            'total_revenue' => CartUpsellRule::sum('total_revenue'),
        ];

        return view('cart::admin.upsell_rules.index', compact('rules', 'metrics'));
    }

    public function create()
    {
        $rule = new CartUpsellRule([
            'status' => true,
            'trigger_type' => 'product_to_product',
            'discount_type' => 'none',
        ]);

        $categories = \Modules\Category\Entities\Category::treeList();

        return view('cart::admin.upsell_rules.create', [
            'rule' => $rule,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('[UPSSELL] store.raw', [
            'all' => $request->all(),
        ]);

        $data = $this->validateData($request);
        \Log::info('[UPSSELL] store.payload', [
            'data' => $data,
            'db' => \DB::connection()->getDatabaseName(),
        ]);

        $rule = CartUpsellRule::create($data);

        // Dinamik teklifleri kaydet
        $this->syncOffers($rule, $request->input('offers', []));

        \Log::info('[UPSSELL] store.created', [
            'id' => optional($rule)->id,
            'total' => CartUpsellRule::count(),
        ]);

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans('cart::upsell.admin_title')]));
    }

    public function edit($id)
    {
        $rule = CartUpsellRule::with([
            'mainProduct.files',
            'upsellProduct.files',
            'upsellProduct.variants.files',
            'preselectedVariant.files',
            'mainCategory',
            'offers.product.saleUnit',
            'offers.product.variants.files',
            'offers.product.files',
            'offers.variant.files'
        ])
            ->withoutGlobalScope('active')
            ->findOrFail($id);

        $categories = \Modules\Category\Entities\Category::treeList();
        
        \Log::info('[UPSELL] edit - loaded offers', [
            'rule_id' => $rule->id,
            'offers_count' => $rule->offers->count(),
            'offers_detail' => $rule->offers->map(function($offer) {
                return [
                    'id' => $offer->id,
                    'product_id' => $offer->product_id,
                    'product_name' => $offer->product->name ?? null,
                    'product_has_image' => $offer->product && $offer->product->base_image ? true : false,
                    'variant_id' => $offer->variant_id,
                    'variant_name' => $offer->variant->name ?? null,
                    'variant_has_image' => $offer->variant && $offer->variant->base_image ? true : false,
                    'discount_type' => $offer->discount_type,
                    'discount_value' => $offer->discount_value,
                    'subtitle' => $offer->subtitle,
                ];
            })->toArray(),
        ]);

        return view('cart::admin.upsell_rules.edit', compact('rule', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $rule = CartUpsellRule::withoutGlobalScope('active')->findOrFail($id);

        \Log::info('[UPSSELL] update.raw', [
            'id' => $id,
            'all' => $request->all(),
        ]);

        $data = $this->validateData($request);
        
        \Log::info('[UPSSELL] update.payload', [
            'data' => $data,
        ]);

        $rule->update($data);

        // Dinamik teklifleri güncelle
        $this->syncOffers($rule, $request->input('offers', []));

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::resource.updated', ['resource' => trans('cart::upsell.admin_title')]));
    }

    public function destroy($id)
    {
        $rule = CartUpsellRule::withoutGlobalScope('active')->findOrFail($id);
        $rule->delete();

        return redirect()->route('admin.cart_upsell_rules.index')
            ->withSuccess(trans('admin::messages.resource_deleted', ['resource' => trans('cart::upsell.admin_title')]));
    }

    protected function validateData(Request $request): array
    {
        $locale = locale();

        $validated = $request->validate([
            'status' => ['sometimes', 'boolean'],
            'trigger_type' => ['required', 'string'],
            'main_product_id' => ['required_if:trigger_type,product_to_product', 'nullable', 'integer', 'exists:products,id'],
            'main_category_id' => ['required_if:trigger_type,category_to_product', 'nullable', 'integer', 'exists:categories,id'],
            'upsell_product_id' => ['required', 'integer', 'exists:products,id'],
            'preselected_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'discount_type' => ['required', 'in:none,percent,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_base_price' => ['nullable', 'in:normal_price,special_price'],
            'title' => ['nullable', 'string'],
            'subtitle' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'show_on' => ['required', 'in:checkout,post_checkout,product'],
            'min_cart_total' => ['nullable', 'numeric', 'min:0'],
            'max_cart_total' => ['nullable', 'numeric', 'min:0'],
            'hide_if_already_in_cart' => ['nullable', 'boolean'],
            'exclude_discounted_products' => ['nullable', 'boolean'],
            'has_countdown' => ['nullable', 'boolean'],
            'countdown_minutes' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $title = $request->input('title');
        $subtitle = $request->input('subtitle');
        $description = $request->input('description');

        $validated['status'] = (bool) $request->input('status', false);
        $validated['hide_if_already_in_cart'] = (bool) $request->input('hide_if_already_in_cart', true);
        $validated['exclude_discounted_products'] = (bool) $request->input('exclude_discounted_products', false);
        $validated['has_countdown'] = (bool) $request->input('has_countdown', false);
        $validated['discount_value'] = $validated['discount_value'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $validated['title'] = $title ? [$locale => $title] : null;
        $validated['subtitle'] = $subtitle ? [$locale => $subtitle] : null;
        $validated['description'] = $description ? [$locale => $description] : null;

        return $validated;
    }

    protected function syncOffers(CartUpsellRule $rule, array $offers)
    {
        \Log::info('[UPSELL] syncOffers called', [
            'rule_id' => $rule->id,
            'offers_count' => count($offers),
            'offers_data' => $offers,
        ]);
        
        $locale = locale();
        $existingOfferIds = [];

        foreach ($offers as $offerData) {
            \Log::info('[UPSELL] Processing offer', ['offer_data' => $offerData]);
            
            // Boş ürün ID'si olanları atla
            if (empty($offerData['product_id'])) {
                \Log::warning('[UPSELL] Skipping offer - empty product_id');
                continue;
            }

            $offerAttributes = [
                'rule_id' => $rule->id,
                'order' => $offerData['order'] ?? 0,
                'trigger' => $offerData['trigger'] ?? 'rejected',
                'product_id' => $offerData['product_id'],
                'variant_id' => !empty($offerData['variant_id']) ? $offerData['variant_id'] : null,
                'discount_type' => $offerData['discount_type'] ?? 'none',
                'discount_value' => $offerData['discount_value'] ?? 0,
                'discount_base_price' => $offerData['discount_base_price'] ?? 'special_price',
                'subtitle' => !empty($offerData['subtitle']) ? [$locale => $offerData['subtitle']] : null,
                'hide_if_in_cart' => isset($offerData['hide_if_in_cart']) ? (bool) $offerData['hide_if_in_cart'] : true,
            ];

            if (!empty($offerData['id'])) {
                // Mevcut teklifi güncelle
                $offer = \Modules\Cart\Entities\CartUpsellOffer::find($offerData['id']);
                if ($offer && $offer->rule_id == $rule->id) {
                    \Log::info('[UPSELL] Updating existing offer', ['offer_id' => $offer->id]);
                    $offer->update($offerAttributes);
                    $existingOfferIds[] = $offer->id;
                } else {
                    \Log::warning('[UPSELL] Offer not found or wrong rule', ['offer_id' => $offerData['id']]);
                }
            } else {
                // Yeni teklif oluştur
                \Log::info('[UPSELL] Creating new offer');
                $offer = \Modules\Cart\Entities\CartUpsellOffer::create($offerAttributes);
                $existingOfferIds[] = $offer->id;
            }
        }

        // Silinmiş teklifleri kaldır
        $rule->offers()->whereNotIn('id', $existingOfferIds)->delete();
    }
}
