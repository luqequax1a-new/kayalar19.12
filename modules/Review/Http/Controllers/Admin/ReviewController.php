<?php

namespace Modules\Review\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Modules\Review\Entities\Review;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Review\Http\Requests\UpdateReviewRequest;
use Illuminate\Support\Str;

class ReviewController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'review::reviews.review';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'review::admin';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = UpdateReviewRequest::class;


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $review = Review::withoutGlobalScope('approved')->findOrFail($id);

        $tabs = TabManager::get('reviews');

        return view('review::admin.edit', compact('review', 'tabs'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function update($id)
    {
        $review = Review::withoutGlobalScope('approved')->findOrFail($id);

        $review->update(request()->except(array_keys(request()->query())));

        return back()->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }


    public function search(Request $request)
    {
        $query = trim((string) $request->get('query', ''));

        if ($query === '') {
            return [];
        }

        $limit = (int) $request->get('limit', 10);
        $limit = $limit > 0 ? min($limit, 50) : 10;

        $items = Review::withoutGlobalScope('approved')
            ->with(['product' => function ($q) {
                $q->withoutGlobalScope('active');
            }])
            ->where(function ($q) use ($query) {
                $q->where('reviewer_name', 'like', "%{$query}%")
                    ->orWhere('comment', 'like', "%{$query}%")
                    ->orWhere('id', (int) $query);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $items->map(function (Review $review) {
            $productName = (string) optional($review->product)->name;
            $reviewer = trim((string) ($review->reviewer_name ?? ''));
            $comment = trim((string) ($review->comment ?? ''));

            $name = '#' . (int) $review->id;
            if ($reviewer !== '') {
                $name .= ' - ' . $reviewer;
            }
            if ($productName !== '') {
                $name .= ' - ' . $productName;
            }
            if ($comment !== '') {
                $name .= ' - ' . Str::limit($comment, 60);
            }

            return [
                'id' => (int) $review->id,
                'name' => $name,
            ];
        })->values();
    }


    /**
     * Destroy resources by given ids.
     *
     * @param string $ids
     *
     * @return void
     */
    public function destroy(string $ids)
    {
        Review::withoutGlobalScope('approved')
            ->whereIn('id', explode(',', $ids))
            ->delete();
    }
}
