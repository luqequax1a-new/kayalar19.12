<?php

namespace Modules\Setting\Entities;

use Modules\Support\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Modules\Setting\Events\SettingSaved;
use Modules\Support\Eloquent\Translatable;
use Illuminate\Database\Eloquent\Collection;
use Modules\Setting\Services\SettingsCacheService;

class Setting extends Model
{
    use Translatable;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['key', 'is_translatable', 'plain_value'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_translatable' => 'boolean',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => SettingSaved::class,
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatedAttributes = ['value'];


    /**
     * Get all settings with cache support.
     *
     * @return Collection
     */
    public static function allCached()
    {
        return app(SettingsCacheService::class)->all(locale());
    }


    /**
     * Determine if the given setting key exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function has($key)
    {
        return static::where('key', $key)->exists();
    }


    /**
     * Get setting for the given key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return string|array
     */
    public static function get($key, $default = null)
    {
        return app(SettingsCacheService::class)->get($key, $default, locale());
    }


    /**
     * Set the given settings.
     *
     * @param array $settings
     *
     * @return void
     */
    public static function setMany($settings)
    {
        foreach ($settings as $key => $value) {
            self::set($key, $value);
        }
    }


    /**
     * Set the given setting.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public static function set($key, $value)
    {
        if ($key === 'translatable') {
            return static::setTranslatableSettings($value);
        }

        static::updateOrCreate(['key' => $key], ['plain_value' => $value]);
    }


    /**
     * Set a translatable settings.
     *
     * @param array $settings
     *
     * @return void
     */
    public static function setTranslatableSettings($settings = [])
    {
        foreach ($settings as $key => $value) {
            static::updateOrCreate(['key' => $key], [
                'is_translatable' => true,
                'value' => $value,
            ]);
        }
    }


    /**
     * Get the value of the setting.
     *
     * @return mixed
     */
    public function getValueAttribute()
    {
        if ($this->is_translatable) {
            return $this->translateOrDefault(locale())->value ?? null;
        }

        if (is_null($this->plain_value)) {
            return null;
        }

        try {
            return unserialize($this->plain_value);
        } catch (\Exception $e) {
            // Eğer veri serialize edilmemişse (manuel SQL ile girilmişse) 
            // JSON olup olmadığını kontrol et, değilse direkt döndür.
            $decoded = json_decode($this->plain_value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->plain_value;
        }
    }


    /**
     * Set the value of the setting.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setPlainValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['plain_value'] = serialize($value);
        } else {
            $this->attributes['plain_value'] = $value;
        }
    }
}
