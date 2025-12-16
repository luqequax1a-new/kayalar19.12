<?php

namespace Modules\SizeChart\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartAssignment extends Model
{
    protected $fillable = [
        'size_chart_id',
        'assignable_type',
        'assignable_id',
        'priority',
    ];

    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }
}
