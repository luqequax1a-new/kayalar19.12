<?php

namespace Modules\Unit\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\Product\Entities\Product;

class Unit extends Model
{
    protected $fillable = [
        'code',
        'name',
        'label',
        'info',
        'info_top',
        'info_bottom',
        'step',
        'min',
        'default_qty',
        'is_default',
        'is_decimal_stock',
        'short_suffix',
    ];

    protected $casts = [
        'step' => 'decimal:2',
        'min' => 'decimal:2',
        'default_qty' => 'decimal:2',
        'is_default' => 'boolean',
        'is_decimal_stock' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'sale_unit_id');
    }

    public function isDecimalStock(): bool
    {
        return (bool) $this->is_decimal_stock;
    }

    public function normalizeQuantity(float $qty): float
    {
        $min = (float) $this->min;
        $step = (float) $this->step;

        if ($qty < $min) {
            $qty = $min;
        }

        if ($step <= 0) {
            if ($this->isDecimalStock()) {
                $step = 0.5;
            } else {
                return $qty;
            }
        }

        $steps = round(($qty - $min) / $step);
        $normalized = $min + $steps * $step;

        return $this->isDecimalStock() ? round($normalized, 2) : $normalized;
    }

    public function isValidQuantity(float $qty): bool
    {
        $min = (float) $this->min;

        if ($qty < $min) {
            return false;
        }

        return true;
    }

    public function getDisplaySuffix(): string
    {
        if (!empty($this->short_suffix)) {
            return $this->short_suffix;
        }

        return $this->label ?: '';
    }

    protected static function booted(): void
    {
        static::creating(function (Unit $unit) {
            if (empty($unit->code)) {
                $base = Str::slug($unit->name ?: uniqid('unit'), '_');
                $candidate = $base;
                $i = 1;
                while (static::where('code', $candidate)->exists()) {
                    $candidate = $base . '_' . $i++;
                }
                $unit->code = $candidate;
            }
        });

        // default unit feature disabled: keep column for compatibility but no enforcement
    }

    public function table()
    {
        return new \Modules\Unit\Entities\Admin\UnitTable($this->newQuery());
    }
}

