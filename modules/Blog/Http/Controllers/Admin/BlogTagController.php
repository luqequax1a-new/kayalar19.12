<?php

namespace Modules\Blog\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Blog\Entities\BlogTag;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Blog\Http\Requests\SaveBlogTagRequest;

class BlogTagController extends Controller
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = BlogTag::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'blog::blog.tags.name';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'blog::admin.tags';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveBlogTagRequest::class;


    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->except(array_keys(request()->query()))
        );

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'id' => $entity->id,
                'name' => $entity->name,
            ], 200);
        }

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo($entity);
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }
}
