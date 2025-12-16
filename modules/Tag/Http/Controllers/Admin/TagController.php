<?php

namespace Modules\Tag\Http\Controllers\Admin;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Tag\Entities\Tag;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Tag\Http\Requests\SaveTagRequest;

class TagController
{
    use HasCrudActions;

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $attributes = $this->getRequest('store')->except(array_keys(request()->query()));

        $existing = null;

        if (request()->wantsJson()) {
            $attributes['name'] = (string) array_get($attributes, 'name');
            $attributes['name'] = Str::of($attributes['name'])->trim()->toString();

            request()->merge(['name' => $attributes['name']]);
            request()->validate([
                'name' => ['required', 'string', 'max:255'],
            ]);

            $lowerName = Str::of($attributes['name'])->lower()->toString();
            $existing = $this->getModel()
                ->whereHas('translations', function ($query) use ($lowerName) {
                    $query
                        ->where('locale', locale())
                        ->whereRaw('LOWER(name) = ?', [$lowerName]);
                })
                ->first();
        }

        $entity = $existing ?: $this->getModel()->create($attributes);

        try {
            Cache::tags('tags')->flush();
        } catch (\Throwable $e) {
        }

        $this->searchable($entity);

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo($entity);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('admin::messages.resource_created', ['resource' => $this->getLabel()]),
                    'tag' => [
                        'id' => $entity->id,
                        'name' => $entity->name,
                    ],
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Tag::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'tag::tags.tag';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'tag::admin.tags';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveTagRequest::class;
}
