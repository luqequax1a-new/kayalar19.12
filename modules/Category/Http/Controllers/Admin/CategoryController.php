<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Category\Entities\Category;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Category\Http\Requests\SaveCategoryRequest;

class CategoryController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'category::categories.category';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'category::admin.categories';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveCategoryRequest::class;


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        // Return virtual "Main Category" for /products page
        if ($id == 0) {
            return $this->getMainCategoryData();
        }

        return Category::with('files')->withoutGlobalScope('active')->find($id);
    }

    /**
     * Get virtual main category data for /products page.
     *
     * @return object
     */
    private function getMainCategoryData()
    {
        return (object) [
            'id' => 0,
            'name' => setting('products_page_name', trans('category::categories.main_category')),
            'slug' => setting('products_page_slug', 'products'),
            'is_searchable' => false,
            'is_active' => true,
            'description' => setting('products_page_description', ''),
            'meta_title' => setting('products_page_meta_title', ''),
            'meta_description' => setting('products_page_meta_description', ''),
            'faq_items' => json_decode(setting('products_page_faq_items', '[]'), true) ?: [],
            'logo' => (object) ['exists' => false],
            'banner' => (object) ['exists' => false],
        ];
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param SaveCategoryRequest $request
     *
     * @return Response
     */
    public function store(SaveCategoryRequest $request)
    {
        // Main category cannot be created, only updated
        if ($request->id == 0) {
            return $this->updateMainCategory($request);
        }

        // Use trait's store logic
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $request->except(array_keys(request()->query()))
        );

        $this->searchable($entity);

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @param SaveCategoryRequest $request
     *
     * @return Response
     */
    public function update($id, SaveCategoryRequest $request)
    {
        // Save main category data to settings
        if ($id == 0) {
            return $this->updateMainCategory($request);
        }

        // Use trait's update logic
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $entity->update(
            $request->except(array_keys(request()->query()))
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }

    /**
     * Update main category settings.
     *
     * @param SaveCategoryRequest $request
     *
     * @return Response
     */
    private function updateMainCategory($request)
    {
        setting([
            'products_page_name' => $request->name ?? trans('storefront::products.shop'),
            'products_page_slug' => $request->slug ?? 'products',
            'products_page_description' => $request->description ?? '',
            'products_page_meta_title' => $request->meta_title ?? '',
            'products_page_meta_description' => $request->meta_description ?? '',
            'products_page_faq_items' => json_encode($request->faq_items ?? []),
        ]);

        return redirect()->route('admin.categories.index')
            ->withSuccess(trans('category::messages.category_saved'));
    }

    /**
     * Destroy resources by given ids.
     *
     * @param string $ids
     *
     * @return Response
     */
    public function destroy(string $ids)
    {
        // Prevent deletion of main category
        if ($ids == 0) {
            return back()->withError('Ana kategori silinemez.');
        }

        Category::withoutGlobalScope('active')
            ->findOrFail($ids)
            ->delete();

        return back()->withSuccess(trans('admin::messages.resource_deleted', ['resource' => $this->getLabel()]));
    }
}
