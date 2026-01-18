<?php

namespace Modules\Category\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Responsable;

class CategoryTreeResponse implements Responsable
{
    /**
     * Categories collection.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $categories;


    /**
     * Create a new instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     */
    public function __construct($categories)
    {
        $this->categories = $categories;
    }


    /**
     * Create an HTTP response that represents the object.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function toResponse($request)
    {
        return response()->json($this->transform());
    }


    /**
     * Transform the categories.
     *
     * @return Collection
     */
    private function transform()
    {
        // Add virtual "Main Category" for /products page
        $mainCategory = [
            'id' => 0,
            'parent' => '#',
            'text' => trans('category::categories.main_category'),
            'data' => [
                'position' => -1,
            ],
        ];

        $transformedCategories = $this->categories->map(function ($category) {
            return [
                'id' => $category->id,
                'parent' => $category->parent_id ?: '#',
                'text' => $category->name,
                'data' => [
                    'position' => $category->position,
                ],
            ];
        });

        // Prepend main category to the beginning
        return collect([$mainCategory])->merge($transformedCategories);
    }
}
