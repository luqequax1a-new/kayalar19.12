<?php

namespace Modules\Storefront\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Support\Entities\UrlSlug;
use Modules\Product\Entities\Product;
use Modules\Category\Entities\Category;
use Modules\Page\Entities\Page;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Category\Http\Controllers\CategoryProductController;
use Modules\Page\Http\Controllers\PageController;
use Modules\Product\Filters\ProductFilter;

class UrlResolverController extends Controller
{
    /**
     * Resolve clean URL to appropriate entity
     */
    public function resolve($slug = null)
    {
        if (is_null($slug) || $slug === '') {
            // If it's the root, maybe try to show home, but ideally it should have hit the home route
            if (request()->getPathInfo() === '/' || request()->getPathInfo() === '') {
                return app(\Modules\Page\Http\Controllers\HomeController::class)->index();
            }
            
            abort(404);
        }

        // Find the entity by slug
        $urlSlug = UrlSlug::findBySlug($slug);
        
        if (!$urlSlug) {
            abort(404);
        }
        
        // Route to appropriate controller based on type
        switch ($urlSlug->type) {
            case 'product':
                return $this->resolveProduct($slug);
                
            case 'category':
                return $this->resolveCategory($slug);
                
            case 'brand':
                return $this->resolveBrand($slug);
                
            case 'page':
                return $this->resolvePage($slug);
                
            default:
                abort(404);
        }
    }
    
    /**
     * Resolve product
     */
    private function resolveProduct($slug)
    {
        $controller = app(ProductController::class);
        $upsellService = app(\Modules\Cart\Services\CartUpsellService::class);
        
        return $controller->show($slug, $upsellService);
    }
    
    /**
     * Resolve category
     */
    private function resolveCategory($slug)
    {
        $controller = app(CategoryProductController::class);
        $model = app(Product::class);
        $filter = app(ProductFilter::class);
        
        return $controller->index($slug, $model, $filter);
    }
    
    /**
     * Resolve page
     */
    private function resolvePage($slug)
    {
        $controller = app(PageController::class);
        
        return $controller->show($slug);
    }

    /**
     * Resolve brand
     */
    private function resolveBrand($slug)
    {
        $controller = app(\Modules\Brand\Http\Controllers\BrandProductController::class);
        $model = app(Product::class);
        $filter = app(ProductFilter::class);
        
        return $controller->index($slug, $model, $filter);
    }
}
