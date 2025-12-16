<?php

namespace Modules\Review\Entities;

use Illuminate\Http\Request;
use Modules\User\Entities\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;
use Modules\Review\Admin\ReviewTable;
use Modules\Media\Eloquent\HasMedia;
 

class Review extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['rating_percent', 'status', 'created_at_formatted'];

    use HasMedia;


    public static function countAndAvgRating(Product $product)
    {
        $row = self::query()
            ->where('product_id', $product->id)
            ->selectRaw('count(*) as count')
            ->selectRaw('avg(rating) as avg_rating')
            ->selectRaw('sum(case when rating = 5 then 1 else 0 end) as count_5')
            ->selectRaw('sum(case when rating = 4 then 1 else 0 end) as count_4')
            ->selectRaw('sum(case when rating = 3 then 1 else 0 end) as count_3')
            ->selectRaw('sum(case when rating = 2 then 1 else 0 end) as count_2')
            ->selectRaw('sum(case when rating = 1 then 1 else 0 end) as count_1')
            ->first();

        return (object) [
            'count' => (int) ($row->count ?? 0),
            'avg_rating' => (float) ($row->avg_rating ?? 0),
            'count_5' => (int) ($row->count_5 ?? 0),
            'count_4' => (int) ($row->count_4 ?? 0),
            'count_3' => (int) ($row->count_3 ?? 0),
            'count_2' => (int) ($row->count_2 ?? 0),
            'count_1' => (int) ($row->count_1 ?? 0),
        ];
    }


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('approved', function ($query) {
            $query->where('is_approved', true);
        });
    }


    public function getAvgRatingAttribute($avgRating)
    {
        return $avgRating ?: 0;
    }


    public function getRatingPercentAttribute()
    {
        return ($this->rating / 5) * 100;
    }


    public function getStatusAttribute()
    {
        return $this->status();
    }


    public function status()
    {
        if ($this->is_approved) {
            return trans('review::statuses.approved');
        }

        return trans('review::statuses.unapproved');
    }


    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d.m.Y') : null;
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }



    /**
     * Get table data for the resource
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function table(Request $request)
    {
        $query = static::withoutGlobalScope('approved')
            ->with(['product' => function ($query) {
                $query->withoutGlobalScope('active');
            }])
            ->when($request->productId, function ($query) use ($request) {
                return $query->where('product_id', $request->productId);
            });

        return new ReviewTable($query);
    }

    
    public function extractMediaFromRequest(): mixed
    {
        $payload = [];
        if (request()->hasFile('images')) {
            // Attach uploaded files under zone 'review_images' after controller persists File records
            // Here we only map to expected structure if IDs are passed
            $fileIds = array_filter((array) request('files.review_images', []));
            if (!empty($fileIds)) {
                $payload['review_images'] = $fileIds;
            }
        }
        return $payload;
    }
}
