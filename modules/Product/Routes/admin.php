<?php

use Illuminate\Support\Facades\Route;

Route::get('products', [
    'as' => 'admin.products.index',
    'uses' => 'ProductController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get('products/stats', [
    'as' => 'admin.products.stats',
    'uses' => 'ProductController@stats',
    'middleware' => 'can:admin.products.index',
]);

// Enhanced Product Listing Routes
Route::get('products/enhanced', [
    'as' => 'admin.products.enhanced.index',
    'uses' => 'EnhancedProductController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get('products/enhanced/table', [
    'as' => 'admin.products.enhanced.table',
    'uses' => 'EnhancedProductController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::post('products/enhanced/bulk-action', [
    'as' => 'admin.products.enhanced.bulk_action',
    'uses' => 'EnhancedProductController@bulkAction',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('products/csv/export', [
    'as' => 'admin.products.csv.export',
    'uses' => 'ProductCsvController@export',
    'middleware' => 'can:admin.products.index',
]);

Route::get('products/excel/export', [
    'as' => 'admin.products.excel.export',
    'uses' => 'ProductExcelController@export',
    'middleware' => 'can:admin.products.index',
]);

Route::get('products/excel/export/trendyol', [
    'as' => 'admin.products.excel.export.trendyol',
    'uses' => 'ProductExcelController@exportTrendyol',
    'middleware' => 'can:admin.products.index',
]);

Route::get('products/excel/export/hepsiburada', [
    'as' => 'admin.products.excel.export.hepsiburada',
    'uses' => 'ProductExcelController@exportHepsiburada',
    'middleware' => 'can:admin.products.index',
]);

Route::post('products/csv/import/upload', [
    'as' => 'admin.products.csv.import.upload',
    'uses' => 'ProductCsvController@upload',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/csv/import/preview', [
    'as' => 'admin.products.csv.import.preview',
    'uses' => 'ProductCsvController@preview',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/csv/import/process', [
    'as' => 'admin.products.csv.import.process',
    'uses' => 'ProductCsvController@process',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('products/csv/simple-import', [
    'as' => 'admin.products.csv.simple_import.form',
    'uses' => 'ProductCsvController@simpleImportForm',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/csv/simple-import', [
    'as' => 'admin.products.csv.simple_import',
    'uses' => 'ProductCsvController@simpleImport',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/excel/import', [
    'as' => 'admin.products.excel.import',
    'uses' => 'ProductExcelController@import',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('products/variants/csv/export', [
    'as' => 'admin.products.variants.csv.export',
    'uses' => 'ProductCsvController@exportVariants',
    'middleware' => 'can:admin.products.index',
]);

Route::post('products/variants/csv/import/upload', [
    'as' => 'admin.products.variants.csv.import.upload',
    'uses' => 'ProductCsvController@uploadVariantCsv',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/variants/csv/import/preview', [
    'as' => 'admin.products.variants.csv.import.preview',
    'uses' => 'ProductCsvController@previewVariantCsv',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/variants/csv/import/process', [
    'as' => 'admin.products.variants.csv.import.process',
    'uses' => 'ProductCsvController@processVariantCsv',
    'middleware' => 'can:admin.products.edit',
]);

Route::get(
    'products/create',
    [
        'as' => 'admin.products.create',
        'uses' => 'ProductController@create',
        'middleware' => 'can:admin.products.create',
    ]
);

Route::post('products', [
    'as' => 'admin.products.store',
    'uses' => 'ProductController@store',
    'middleware' => 'can:admin.products.create',
]);

Route::get('products/{id}/edit', [
    'as' => 'admin.products.edit',
    'uses' => 'ProductController@edit',
    'middleware' => 'can:admin.products.edit',
]);

Route::put('products/{id}', [
    'as' => 'admin.products.update',
    'uses' => 'ProductController@update',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/status', [
    'as' => 'admin.products.status',
    'uses' => 'ProductController@status',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/brand', [
    'as' => 'admin.products.brand',
    'uses' => 'ProductController@updateBrand',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/{id}/duplicate', [
    'as' => 'admin.products.duplicate',
    'uses' => 'ProductController@duplicate',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('products/{ids}', [
    'as' => 'admin.products.destroy',
    'uses' => 'ProductController@destroy',
    'middleware' => 'can:admin.products.destroy',
]);

Route::get('products/index/table', [
    'as' => 'admin.products.table',
    'uses' => 'ProductController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::post('products/bulk-status', [
    'as' => 'admin.products.bulk_status',
    'uses' => 'ProductController@bulkStatus',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/bulk-update-price', [
    'as' => 'admin.products.bulk_update_price',
    'uses' => 'ProductController@bulkUpdatePrice',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/bulk-update-special-price', [
    'as' => 'admin.products.bulk_update_special_price',
    'uses' => 'ProductController@bulkUpdateSpecialPrice',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/bulk-update-stock', [
    'as' => 'admin.products.bulk_update_stock',
    'uses' => 'ProductController@bulkUpdateStock',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/bulk-delete', [
    'as' => 'admin.products.bulk_delete',
    'uses' => 'ProductController@bulkDelete',
    'middleware' => 'can:admin.products.destroy',
]);

Route::post('products/bulk-preview', [
    'as' => 'admin.products.bulk_preview',
    'uses' => 'ProductController@bulkPreview',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('products/bulk-update', [
    'as' => 'admin.products.bulk_update',
    'uses' => 'ProductController@bulkUpdate',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('google-taxonomy', [
    'as' => 'admin.google_taxonomy.index',
    'uses' => 'TaxonomyController@index',
    'middleware' => 'can:admin.products.index',
]);
Route::get('products/{id}/inventory', [
    'as' => 'admin.products.inventory.show',
    'uses' => 'ProductController@inventory',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/inventory', [
    'as' => 'admin.products.inventory.update',
    'uses' => 'ProductController@updateInventory',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('products/{id}/pricing', [
    'as' => 'admin.products.pricing.show',
    'uses' => 'ProductController@pricing',
    'middleware' => 'can:admin.products.edit',
]);

Route::patch('products/{id}/pricing', [
    'as' => 'admin.products.pricing.update',
    'uses' => 'ProductController@updatePricing',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('redirects', [
    'as' => 'admin.redirects.index',
    'uses' => 'RedirectController@index',
    'middleware' => 'can:admin.products.index',
]);

Route::get('redirects/index/table', [
    'as' => 'admin.redirects.table',
    'uses' => 'RedirectController@table',
    'middleware' => 'can:admin.products.index',
]);

Route::get('redirects/create', [
    'as' => 'admin.redirects.create',
    'uses' => 'RedirectController@create',
    'middleware' => 'can:admin.products.create',
]);

Route::post('redirects', [
    'as' => 'admin.redirects.store',
    'uses' => 'RedirectController@store',
    'middleware' => 'can:admin.products.create',
]);

Route::get('redirects/{id}/edit', [
    'as' => 'admin.redirects.edit',
    'uses' => 'RedirectController@edit',
    'middleware' => 'can:admin.products.edit',
]);

Route::put('redirects/{id}', [
    'as' => 'admin.redirects.update',
    'uses' => 'RedirectController@update',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('redirects/{ids}', [
    'as' => 'admin.redirects.destroy',
    'uses' => 'RedirectController@destroy',
    'middleware' => 'can:admin.products.destroy',
]);

Route::patch('redirects/{id}/status', [
    'as' => 'admin.redirects.status',
    'uses' => 'RedirectController@status',
    'middleware' => 'can:admin.products.edit',
]);

Route::get('categories/{category}/products-for-sorting', [
    'as' => 'admin.categories.products.sorting',
    'uses' => 'ProductController@productsForSorting',
    'middleware' => 'can:admin.products.edit',
]);

Route::post('categories/{category}/product-order', [
    'as' => 'admin.categories.products.order',
    'uses' => 'ProductController@saveProductOrder',
    'middleware' => 'can:admin.products.edit',
]);

Route::delete('categories/{category}/product-order', [
    'as' => 'admin.categories.products.order.reset',
    'uses' => 'ProductController@resetProductOrder',
    'middleware' => 'can:admin.products.edit',
]);
