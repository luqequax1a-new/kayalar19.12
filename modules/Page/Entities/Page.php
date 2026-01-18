<?php

namespace Modules\Page\Entities;

use Spatie\Sitemap\Tags\Url;
use Illuminate\Support\Carbon;
use Modules\Admin\Ui\AdminTable;
use Illuminate\Http\JsonResponse;
use Modules\Support\Eloquent\Model;
use Modules\Meta\Eloquent\HasMetaData;
use Modules\Support\Eloquent\Sluggable;
use Spatie\Sitemap\Contracts\Sitemapable;
use Modules\Support\Eloquent\Translatable;

class Page extends Model implements Sitemapable
{
    use Translatable, Sluggable, HasMetaData;

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
    protected $fillable = ['slug', 'is_active'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    protected $translatedAttributes = ['name', 'body'];

    /**
     * The attribute that will be slugged.
     *
     * @var string
     */
    protected $slugAttribute = 'name';


    public static function urlForPage($id)
    {
        return static::select('slug')->firstOrNew(['id' => $id])->url();
    }


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addActiveGlobalScope();
        
        // Register slug observer
        static::observe(\Modules\Page\Observers\PageSlugObserver::class);
    }


    public function url()
    {
        if (is_null($this->slug)) {
            return '#';
        }

        return localized_url(locale(), $this->slug);
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table()
    {
        return new \Modules\Page\Admin\PageTable($this->newQuery()->withoutGlobalScope('active'));
    }


    public function toSitemapTag(): Url|string|array
    {
        if (! is_string($this->slug) || trim($this->slug) === '') {
            return [];
        }

        $url = $this->url();

        if (! is_string($url) || trim($url) === '' || trim($url) === '#') {
            return [];
        }

        $changefreq = setting('support.sitemap.pages_changefreq', Url::CHANGE_FREQUENCY_WEEKLY);
        $priority = (float) setting('support.sitemap.pages_priority', 0.5);

        $tag = Url::create($url)
            ->setChangeFrequency($changefreq)
            ->setPriority($priority);

        if (! empty($this->updated_at)) {
            try {
                $tag->setLastModificationDate(
                    $this->updated_at instanceof \DateTimeInterface
                        ? $this->updated_at
                        : Carbon::create($this->updated_at)
                );
            } catch (\Throwable $e) {
            }
        }

        return $tag;
    }

}
