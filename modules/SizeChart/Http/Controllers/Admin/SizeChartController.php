<?php

namespace Modules\SizeChart\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Modules\Tag\Entities\Tag;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Admin\Traits\HasCrudActions;
use Modules\SizeChart\Entities\SizeChart;
use Modules\SizeChart\Entities\SizeChartAssignment;
use Modules\SizeChart\Http\Requests\SaveSizeChartRequest;

class SizeChartController
{
    use HasCrudActions;

    protected string $model = SizeChart::class;

    protected string $label = 'size_chart::size_charts.size_chart';

    protected string $viewPath = 'size_chart::admin.size_charts';

    protected string|array $validation = SaveSizeChartRequest::class;

    public function store(SaveSizeChartRequest $request): RedirectResponse|JsonResponse
    {
        $this->disableSearchSyncing();

        $sizeChart = SizeChart::create($request->except(array_keys($request->query())));

        $this->syncAssignments($sizeChart, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_created', ['resource' => trans($this->label)]),
            ]);
        }

        return redirect()->route('admin.size_charts.index')
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => trans($this->label)]));
    }

    public function update(int $id, SaveSizeChartRequest $request): RedirectResponse|JsonResponse
    {
        $sizeChart = SizeChart::withoutGlobalScope('active')->findOrFail($id);

        $this->disableSearchSyncing();

        $sizeChart->update($request->except(array_keys($request->query())));

        $this->syncAssignments($sizeChart, $request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('admin::messages.resource_updated', ['resource' => trans($this->label)]),
            ]);
        }

        return redirect()->route('admin.size_charts.index')
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans($this->label)]));
    }

    public function searchProducts(Request $request)
    {
        $query = (string) $request->get('query', '');

        return Product::query()
            ->withoutGlobalScope('active')
            ->with(['translations'])
            ->when($query !== '', function ($q) use ($query) {
                $q->whereHas('translations', function ($tq) use ($query) {
                    $tq->where('name', 'like', "%{$query}%");
                });
            })
            ->limit((int) $request->get('limit', 10))
            ->get()
            ->map(function (Product $product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            });
    }

    private function syncAssignments(SizeChart $sizeChart, Request $request): void
    {
        $sizeChart->assignments()->delete();

        $categoryIds = array_filter((array) $request->input('categories', []));
        foreach ($categoryIds as $categoryId) {
            SizeChartAssignment::create([
                'size_chart_id' => $sizeChart->id,
                'assignable_type' => Category::class,
                'assignable_id' => (int) $categoryId,
                'priority' => 0,
            ]);
        }

        $tagIds = array_filter((array) $request->input('tags', []));
        foreach ($tagIds as $tagId) {
            SizeChartAssignment::create([
                'size_chart_id' => $sizeChart->id,
                'assignable_type' => Tag::class,
                'assignable_id' => (int) $tagId,
                'priority' => 0,
            ]);
        }

        $productId = $request->input('product_id');
        if (!empty($productId)) {
            SizeChartAssignment::create([
                'size_chart_id' => $sizeChart->id,
                'assignable_type' => Product::class,
                'assignable_id' => (int) $productId,
                'priority' => 0,
            ]);
        }
    }
}
