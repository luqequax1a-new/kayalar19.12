<?php

namespace Modules\SizeChart\Entities;

use Modules\Tag\Entities\Tag;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Media\Entities\File;
use Modules\Media\Eloquent\HasMedia;
use Modules\Support\Eloquent\Model;
use Modules\Support\Eloquent\Translatable;
use Modules\SizeChart\Admin\SizeChartTable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeChart extends Model
{
    use Translatable, HasMedia;

    public const TYPE_IMAGE = 'image';
    public const TYPE_HTML = 'html';

    protected $with = ['translations'];

    protected $fillable = [
        'is_active',
        'type',
        'content_html',
    ];

    public $translatedAttributes = ['title'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image', 'categories', 'tags', 'product_id'];

    protected static function booted(): void
    {
        static::addActiveGlobalScope();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SizeChartAssignment::class);
    }

    public function getCategoriesAttribute(): array
    {
        return $this->assignments
            ->where('assignable_type', Category::class)
            ->pluck('assignable_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function getTagsAttribute(): array
    {
        return $this->assignments
            ->where('assignable_type', Tag::class)
            ->pluck('assignable_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function getProductIdAttribute(): ?int
    {
        $id = $this->assignments
            ->where('assignable_type', Product::class)
            ->pluck('assignable_id')
            ->first();

        return is_null($id) ? null : (int) $id;
    }

    public function getImageAttribute(): File
    {
        return $this->files->where('pivot.zone', 'size_chart_image')->first() ?: new File();
    }

    public function extractMediaFromRequest(): array
    {
        $files = request('files', []);

        return [
            'size_chart_image' => array_get($files, 'size_chart_image', []),
        ];
    }

    public function table()
    {
        return new SizeChartTable($this->newQuery()->withoutGlobalScope('active'));
    }
}
