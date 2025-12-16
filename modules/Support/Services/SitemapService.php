<?php

namespace Modules\Support\Services;

use Spatie\Sitemap\Sitemap;
use Modules\Page\Entities\Page;
use Spatie\Sitemap\SitemapIndex;
use Modules\Brand\Entities\Brand;
use Modules\Blog\Entities\BlogPost;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Blog\Entities\BlogCategory;

class SitemapService
{
    private array $sitemaps;


    public function __construct()
    {
        $this->sitemaps = [];
    }


    public function generate()
    {
        $appUrl = (string) config('app.url');

        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);

            $scheme = parse_url($appUrl, PHP_URL_SCHEME);

            if (is_string($scheme) && $scheme !== '') {
                URL::forceScheme($scheme);
            }
        }

        try {
            $path = public_path('sitemaps');
            File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
            File::cleanDirectory($path);

            $this->generateSitemaps();

            $sitemapIndex = SitemapIndex::create();

            foreach ($this->sitemaps as $sitemap) {
                $sitemapIndex->add($sitemap);
            }

            $sitemapIndex->writeToFile(public_path('sitemap.xml'));

            $this->updateRobotTxt();

            $this->logSitemapGenerationSummary();
        } finally {
            URL::forceRootUrl(null);
            URL::forceScheme(null);
        }
    }


    private function logSitemapGenerationSummary(): void
    {
        try {
            $files = File::glob(public_path('sitemaps/*.xml')) ?: [];

            $countsByFile = [];

            foreach ($files as $filePath) {
                $count = null;

                try {
                    $xml = @simplexml_load_file($filePath);

                    if ($xml !== false) {
                        $namespaces = $xml->getNamespaces(true);
                        $ns = $namespaces[''] ?? null;

                        if (is_string($ns) && $ns !== '') {
                            $xml->registerXPathNamespace('ns', $ns);
                            $nodes = $xml->xpath('//ns:url') ?: [];
                        } else {
                            $nodes = $xml->xpath('//url') ?: [];
                        }

                        $count = is_array($nodes) ? count($nodes) : null;
                    }
                } catch (\Throwable $e) {
                }

                $countsByFile[basename($filePath)] = $count;
            }

            Log::info('Sitemap generated', [
                'env' => (string) config('app.env'),
                'app_url' => (string) config('app.url'),
                'sitemap_index_count' => count($this->sitemaps),
                'sitemaps' => $countsByFile,
            ]);
        } catch (\Throwable $e) {
        }
    }


    public function generateSitemaps()
    {
        if (setting('support.sitemap.include_products', true)) {
            $this->generateForProducts();
        }

        if (setting('support.sitemap.include_categories', true)) {
            $this->generateForCategories();
        }

        if (setting('support.sitemap.include_brands', true)) {
            $this->generateForBrands();
        }

        if (setting('support.sitemap.include_pages', true)) {
            $this->generateForPages();
        }

        if (setting('support.sitemap.include_blog_posts', true)) {
            $this->generateForBlogPosts();
        }

        if (setting('support.sitemap.include_blog_categories', true)) {
            $this->generateForBlogCategories();
        }
    }


    private function generateForProducts()
    {
        $counter = 1;
        $chunkSize = (int) setting('support.sitemap.products_per_sitemap', 10000);
        $chunkSize = max(100, min($chunkSize, 50000));

        $query = Product::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new Product())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk($chunkSize, function ($products) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_products_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($products)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function generateForCategories()
    {
        $counter = 1;

        $query = Category::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new Category())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk('1000', function ($categories) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_categories_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($categories)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function generateForBrands()
    {
        $counter = 1;

        $query = Brand::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new Brand())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk('1000', function ($brands) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_brands_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($brands)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function generateForPages()
    {
        $counter = 1;

        $query = Page::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new Page())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk('1000', function ($pages) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_pages_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($pages)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function generateForBlogPosts()
    {
        $counter = 1;

        $query = BlogPost::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new BlogPost())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk('1000', function ($blogPosts) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_blog_posts_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($blogPosts)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function generateForBlogCategories()
    {
        $counter = 1;

        $query = BlogCategory::query()
            ->whereNotNull('slug')
            ->where('slug', '!=', '');

        if (Schema::hasColumn((new BlogCategory())->getTable(), 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        $query->chunk('1000', function ($blogCategories) use (&$counter) {
            $outputDir = public_path('sitemaps');
            $sitemapName = 'sitemap_blog_categories_' . $counter++ . '.xml';

            Sitemap::create()
                ->add($blogCategories)
                ->writeToFile("$outputDir/$sitemapName");

            $this->sitemaps[] = url("sitemaps/$sitemapName");
        });
    }


    private function updateRobotTxt()
    {
        $path = public_path('robots.txt');
        $robotTxt = @file_get_contents($path);

        if (strpos($robotTxt, 'Sitemap:')) {
            $robotTxt = preg_replace('/.*Sitemap:.*\n/', 'Sitemap: ' . url('sitemap.xml') . "\n", $robotTxt);
        } else {
            $robotTxt .= "\n" . 'Sitemap: ' . url('sitemap.xml');
        }

        @file_put_contents($path, $robotTxt);
    }
}
