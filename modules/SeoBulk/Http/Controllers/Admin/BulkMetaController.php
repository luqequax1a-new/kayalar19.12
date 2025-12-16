<?php

namespace Modules\SeoBulk\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Modules\Brand\Entities\Brand;
use Modules\Category\Entities\Category;
use Modules\DynamicCategory\Entities\DynamicCategory;
use Modules\Product\Entities\Product;
use Modules\Attribute\Entities\Attribute;
use Modules\SeoBulk\Jobs\BulkSeoMetaJob;
use Modules\SeoBulk\Services\PlaceholderRenderer;

class BulkMetaController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()->orderBy('position')->get(['id','slug'])->map(fn($c)=>['id'=>$c->id,'slug'=>$c->slug]);
        $brands = Brand::query()->get(['id','slug'])->map(fn($b)=>['id'=>$b->id,'slug'=>$b->slug]);
        $attributes = Attribute::query()->get(['id','slug']);
        return view('seo_bulk::admin.bulk_meta.index', compact('categories','brands','attributes'));
    }


    public function execute(Request $request)
    {
        $config = $this->normalizeConfig($request);
        [$productIds,$categoryIds,$dynamicCategoryIds] = $this->collectTargets($config);
        $renderer = new PlaceholderRenderer($config);
        $locale = $config['locale'];
        $overwrite = (bool) ($config['seo_filters']['overwrite'] ?? false);
        $updatedProducts = 0; $updatedCategories = 0;
        $updatedDynamicCategories = 0;

        if (!empty($productIds)) {
            $products = Product::query()->withoutGlobalScope('active')
                ->with(['variants.files','brand','categories','meta','files'])
                ->whereIn('id',$productIds)->get();
            foreach ($products as $p) {
                $title = $renderer->renderProductTitle($p);
                $desc = $renderer->renderProductDescription($p);
                $curTitle = optional($p->meta)->getAttribute('meta_title');
                $curDesc = optional($p->meta)->getAttribute('meta_description');
                $applyTitle = $title && ($overwrite || !$curTitle);
                $applyDesc = $desc && ($overwrite || !$curDesc);
                $data = [];
                if ($applyTitle) $data['meta_title'] = $title;
                if ($applyDesc) $data['meta_description'] = $desc;
                if (!empty($data)) {
                    $meta = $p->meta()->firstOrCreate([]);
                    $meta->fill([$locale=>$data])->save();
                    $updatedProducts++;
                }
                
            }
        }

        if (!empty($categoryIds)) {
            $cats = Category::query()->withoutGlobalScope('active')->with('files')->whereIn('id',$categoryIds)->get();
            foreach ($cats as $c) {
                $title = $renderer->renderCategoryTitle($c);
                $desc = $renderer->renderCategoryDescription($c);
                $curTitle = $c->meta_title; $curDesc = $c->meta_description;
                $applyTitle = $title && ($overwrite || !$curTitle);
                $applyDesc = $desc && ($overwrite || !$curDesc);
                $data = [];
                if ($applyTitle) $data['meta_title'] = $title;
                if ($applyDesc) $data['meta_description'] = $desc;
                if (!empty($data)) {
                    $c->withoutEvents(function () use ($c, $data) {
                        $c->update($data);
                    });
                    $c->clearEntityTaggedCache();
                    $updatedCategories++;
                }
                
            }
        }

        if (!empty($dynamicCategoryIds)) {
            $dyn = DynamicCategory::query()->withoutGlobalScope('active')->whereIn('id', $dynamicCategoryIds)->get();
            foreach ($dyn as $dc) {
                $title = $renderer->renderDynamicCategoryTitle($dc);
                $desc = $renderer->renderDynamicCategoryDescription($dc);
                $curTitle = $dc->meta_title;
                $curDesc = $dc->meta_description;
                $applyTitle = $title && ($overwrite || !$curTitle);
                $applyDesc = $desc && ($overwrite || !$curDesc);
                $data = [];
                if ($applyTitle) $data['meta_title'] = $title;
                if ($applyDesc) $data['meta_description'] = $desc;
                if (!empty($data)) {
                    $dc->withoutEvents(function() use ($dc,$data){ $dc->update($data); });
                    $dc->clearEntityTaggedCache();
                    $updatedDynamicCategories++;
                }
            }
        }

        return response()->json([
            'queued'=>false,
            'updated_products'=>$updatedProducts,
            'updated_categories'=>$updatedCategories,
            'updated_dynamic_categories'=>$updatedDynamicCategories,
            'total'=>($updatedProducts+$updatedCategories+$updatedDynamicCategories),
        ]);
    }

    private function normalizeConfig(Request $request): array
    {
        return [
            'scope_products' => (bool) $request->boolean('scope_products', true),
            'scope_categories' => (bool) $request->boolean('scope_categories', false),
            'selected_categories' => $request->input('selected_categories', []),
            'filters' => [
                'categories' => $request->input('categories', $request->input('selected_categories', [])),
                'brand' => $request->input('brand'),
                'attributes' => $request->input('attributes', []),
                'price_min' => $request->input('price_min'),
                'price_max' => $request->input('price_max'),
                'in_stock' => $request->input('in_stock'),
                'has_variants' => $request->input('has_variants'),
                'is_active' => $request->input('is_active'),
            ],
            'seo_filters' => [
                'empty_title' => (bool) $request->boolean('empty_title'),
                'empty_description' => (bool) $request->boolean('empty_description'),
                'overwrite' => true,
            ],
            'templates' => [
                'title' => $request->input('title_template'),
                'description' => $request->input('description_template'),
                'separator' => $request->input('separator', ' - '),
            ],
            'dry_run' => false,
            'locale' => app()->getLocale(),
        ];
    }

    private function collectTargets(array $config): array
    {
        $pQuery = Product::query()->withoutGlobalScope('active')
            ->with(['variants.files','brand','categories'])
            ->select('id');
        if (!empty($config['filters']['categories'])) {
            $cats = (array) $config['filters']['categories'];
            $ids = array_values(array_filter($cats, fn($x)=>is_numeric($x)));
            $slugsExplicit = array_values(array_filter($cats, fn($x)=>!is_numeric($x)));
            if (!empty($ids) || !empty($slugsExplicit)) {
                $pQuery->whereHas('categories', function($q) use ($ids,$slugsExplicit) {
                    if (!empty($ids)) $q->whereIn('id',$ids);
                    if (!empty($slugsExplicit)) $q->orWhereIn('slug',$slugsExplicit);
                });
            }
        }
        if (!empty($config['filters']['brand'])) {
            $pQuery->whereHas('brand', fn($q)=>$q->where('slug',$config['filters']['brand']));
        }
        if (!empty($config['filters']['attributes'])) {
            foreach ($config['filters']['attributes'] as $slug=>$values) {
                $pQuery->whereHas('attributes', function($q) use ($slug,$values) {
                    $q->whereHas('attribute', fn($aq)=>$aq->where('slug',$slug))
                      ->whereHas('values', fn($vq)=>$vq->whereTranslationIn('value',$values));
                });
            }
        }
        if (!is_null($config['filters']['price_min'])) {
            $pQuery->where('selling_price','>=',(float)$config['filters']['price_min']);
        }
        if (!is_null($config['filters']['price_max'])) {
            $pQuery->where('selling_price','<=',(float)$config['filters']['price_max']);
        }
        if (!is_null($config['filters']['in_stock'])) {
            $pQuery->where('in_stock',(bool)$config['filters']['in_stock']);
        }
        if (!is_null($config['filters']['has_variants'])) {
            $hv = (bool) $config['filters']['has_variants'];
            if ($hv) {
                $pQuery->whereHas('variants');
            } else {
                $pQuery->whereDoesntHave('variants');
            }
        }
        if (!is_null($config['filters']['is_active'])) {
            $pQuery->where('is_active',(bool)$config['filters']['is_active']);
        }
        if ($config['seo_filters']['empty_title']) {
            $pQuery->whereHas('meta', fn($q)=>$q->where(function($qq){$qq->whereNull('meta_title')->orWhere('meta_title','');}));
        }
        if ($config['seo_filters']['empty_description']) {
            $pQuery->whereHas('meta', fn($q)=>$q->where(function($qq){$qq->whereNull('meta_description')->orWhere('meta_description','');}));
        }
        $productIds = $config['scope_products'] ? $pQuery->pluck('id')->all() : [];

        $categoryIds = [];
        $dynamicCategoryIds = [];
        if ($config['scope_categories'] && !empty($config['selected_categories'])) {
            $raw = (array) $config['selected_categories'];
            $catIds = [];
            $dynIds = [];
            foreach ($raw as $v) {
                $s = is_string($v) ? $v : (string) $v;
                if (preg_match('/^c_(\d+)$/', $s, $m)) {
                    $catIds[] = (int) $m[1];
                    continue;
                }
                if (preg_match('/^d_(\d+)$/', $s, $m)) {
                    $dynIds[] = (int) $m[1];
                    continue;
                }
                if (is_numeric($s)) {
                    $catIds[] = (int) $s;
                }
            }

            $cQuery = Category::query()->withoutGlobalScope('active')->select('id');
            if (!empty($catIds)) {
                $cQuery->whereIn('id', $catIds);
            } else {
                $cQuery->whereRaw('1=0');
            }
            if ($config['seo_filters']['empty_title']) {
                $cQuery->where(function($qq){$qq->whereNull('meta_title')->orWhere('meta_title','');});
            }
            if ($config['seo_filters']['empty_description']) {
                $cQuery->where(function($qq){$qq->whereNull('meta_description')->orWhere('meta_description','');});
            }
            $categoryIds = $cQuery->pluck('id')->all();

            $dQuery = DynamicCategory::query()->withoutGlobalScope('active')->select('id');
            if (!empty($dynIds)) {
                $dQuery->whereIn('id', $dynIds);
            } else {
                $dQuery->whereRaw('1=0');
            }
            if ($config['seo_filters']['empty_title']) {
                $dQuery->where(function($qq){$qq->whereNull('meta_title')->orWhere('meta_title','');});
            }
            if ($config['seo_filters']['empty_description']) {
                $dQuery->where(function($qq){$qq->whereNull('meta_description')->orWhere('meta_description','');});
            }
            $dynamicCategoryIds = $dQuery->pluck('id')->all();
        }
        return [$productIds,$categoryIds,$dynamicCategoryIds];
    }

    
}
