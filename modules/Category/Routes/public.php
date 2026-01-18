<?php

use Illuminate\Support\Facades\Route;

Route::get('categories', 'CategoryController@index')->name('categories.index');

// Legacy category URL - redirect to clean URL
Route::get('categories/{category}/products', function($category) {
    // Find category by slug or ID and redirect to clean URL
    $cat = \Modules\Category\Entities\Category::where('slug', $category)
        ->orWhere('id', $category)
        ->first();
    
    if ($cat) {
        return redirect('/' . $cat->slug, 301);
    }
    
    abort(404);
})->name('categories.products.index');
