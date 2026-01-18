<?php

namespace Modules\SizeChart\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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

    public function store(SaveSizeChartRequest $request)
    {
        return DB::transaction(function () use ($request) {
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
        });
    }

    public function update(int $id, SaveSizeChartRequest $request)
    {
        return DB::transaction(function () use ($id, $request) {
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
        });
    }

    public function searchProducts(Request $request)
    {
        $query = (string) $request->get('query', '');

        return Product::query()
            ->withoutGlobalScope('active')
            ->select(['id']) // Optimize query
            ->with(['translations:id,product_id,name']) // Load only necessary fields
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
        // Assignment list to bulk insert
        $assignments = [];
        $now = now();

        $addAssignment = function ($type, $id) use (&$assignments, $sizeChart, $now) {
            if (empty($id)) return;
            $assignments[] = [
                'size_chart_id' => $sizeChart->id,
                'assignable_type' => $type,
                'assignable_id' => (int) $id,
                'priority' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        };

        foreach (array_filter((array) $request->input('categories', [])) as $id) {
            $addAssignment(Category::class, $id);
        }

        foreach (array_filter((array) $request->input('tags', [])) as $id) {
            $addAssignment(Tag::class, $id);
        }

        $productId = $request->input('product_id');
        if (!empty($productId)) {
            $addAssignment(Product::class, $productId);
        }

        // Delete existing assignments
        $sizeChart->assignments()->delete();

        // Insert new ones (if any)
        if (!empty($assignments)) {
            SizeChartAssignment::insert($assignments);
        }
    }
}
