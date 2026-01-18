<?php

namespace Modules\Question\Entities;

use Modules\User\Entities\User;
use Modules\Support\Eloquent\Model;
use Modules\Product\Entities\Product;

class Question extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'customer_name',
        'customer_email',
        'question',
        'answer',
        'is_approved',
        'answered_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_approved' => 'boolean',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the product associated with the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScope('active');
    }

    /**
     * Get the user who asked the question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer name.
     *
     * @return string
     */
    public function getCustomerNameAttribute($value)
    {
        if ($this->user_id && $this->user) {
            return $this->user->full_name;
        }

        return $value;
    }

    /**
     * Get table data for the resource
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function table($request)
    {
        $query = static::with(['product' => function ($query) {
                $query->withoutGlobalScope('active');
            }]);

        return new \Modules\Question\Admin\QuestionTable($query);
    }
}
