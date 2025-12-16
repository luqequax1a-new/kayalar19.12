<?php

namespace Modules\SeoBulk\Http\Controllers\Admin;

use Modules\Category\Entities\Category;
use Modules\DynamicCategory\Entities\DynamicCategory;

class CategoryProxyController
{
    public function index()
    {
        $categories = Category::withoutGlobalScope('active')
            ->orderByRaw('-position DESC')
            ->get();

        $nodes = $categories->map(function ($category) {
            return [
                'id' => 'c_' . $category->id,
                'parent' => $category->parent_id ? ('c_' . $category->parent_id) : '#',
                'text' => $category->name,
                'data' => [
                    'position' => $category->position,
                    'type' => 'category',
                ],
            ];
        })->values()->all();

        $dynamic = DynamicCategory::query()
            ->withoutGlobalScope('active')
            ->orderByDesc('id')
            ->get(['id', 'name']);

        if ($dynamic->isNotEmpty()) {
            $nodes[] = [
                'id' => 'd_root',
                'parent' => '#',
                'text' => 'Dinamik Kategoriler',
                'data' => [
                    'type' => 'dynamic_root',
                ],
            ];

            foreach ($dynamic as $dc) {
                $nodes[] = [
                    'id' => 'd_' . $dc->id,
                    'parent' => 'd_root',
                    'text' => $dc->name,
                    'data' => [
                        'type' => 'dynamic_category',
                    ],
                ];
            }
        }

        return response()->json($nodes);
    }
}

