<?php

namespace Modules\Product\Services\Excel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Modules\Product\Entities\Product;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public function exportProducts(Builder $query, array $columns = []): string
    {
        if (empty($columns)) {
            $columns = $this->getDefaultColumns();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $col = 'A';
        foreach (array_values($columns) as $label) {
            $sheet->setCellValue($col . '1', $label);
            $col++;
        }

        // Rows
        $rowIdx = 2;
        foreach ($query->cursor() as $product) {
            $col = 'A';
            foreach (array_keys($columns) as $key) {
                $sheet->setCellValue($col . $rowIdx, $this->getCellValue($product, $key));
                $col++;
            }
            $rowIdx++;
        }

        $tempId = uniqid('products_export_', true);
        $path = "exports/{$tempId}.xlsx";
        $absolute = Storage::disk('local')->path($path);

        if (!is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolute);

        return $absolute;
    }

    protected function getDefaultColumns(): array
    {
        return [
            'id' => 'ID',
            'sku' => 'SKU',
            'name' => 'Ürün Adı',
            'slug' => 'Slug',
            'brand_name' => 'Marka',
            'category_names' => 'Kategoriler',
            'price' => 'Fiyat',
            'special_price' => 'İndirimli Fiyat',
            'qty' => 'Stok',
            'manage_stock' => 'Stok Takibi',
            'in_stock' => 'Stok Durumu',
            'is_active' => 'Durum',
            'short_description' => 'Kısa Açıklama',
            'description' => 'Açıklama',
            'images' => 'Görseller',
        ];
    }

    protected function getCellValue(Product $product, string $key)
    {
        switch ($key) {
            case 'name':
                return $product->name;
            case 'description':
                return $product->description;
            case 'short_description':
                return $product->short_description;
            case 'brand_name':
                return optional($product->brand)->name;
            case 'category_names':
                return $product->categories->pluck('name')->implode(', ');
            case 'category_ids':
                return $product->categories->pluck('id')->implode(',');
            case 'images':
                return $this->getProductImages($product)->implode(',');
            case 'manage_stock':
                return $product->manage_stock ? 'E' : 'H';
            case 'in_stock':
                return $product->in_stock ? 'E' : 'H';
            case 'is_active':
                return $product->is_active ? 'E' : 'H';
            case 'price':
                return (float) $product->price->amount();
            case 'special_price':
                return $product->special_price->amount() > 0 ? (float) $product->special_price->amount() : '';
            default:
                $val = $product->{$key} ?? null;
                if ($val instanceof \Modules\Support\Money) {
                    return (float) $val->amount();
                }
                return $val;
        }
    }

    protected function getProductImages(Product $product): \Illuminate\Support\Collection
    {
        $paths = $product->files->pluck('path');
        if ($paths->isEmpty()) {
            $paths = $product->productMedia->pluck('path');
        }
        return $paths->map(fn($p) => $this->normalizeUrl($p));
    }

    protected function getVariantImages(\Modules\Product\Entities\ProductVariant $variant, Product $product): \Illuminate\Support\Collection
    {
        $paths = $variant->files->pluck('path');
        if ($paths->isEmpty()) {
            return $this->getProductImages($product);
        }
        return $paths->map(fn($p) => $this->normalizeUrl($p));
    }

    private function normalizeUrl($path): string
    {
        if (!$path) return '';
        if (preg_match('/^https?:\/\//', $path)) return $path;
        return asset('storage/' . $path);
    }

    public function exportTrendyol(Builder $query, string $template = 'general'): string
    {
        $baseTemplate = null;
        if ($template === 'fabric' && file_exists(base_path('kumas.xlsx'))) {
            $baseTemplate = base_path('kumas.xlsx');
        } elseif ($template === 'home_textile' && file_exists(base_path('urun-olusturma-1144531.xlsx'))) {
            $baseTemplate = base_path('urun-olusturma-1144531.xlsx');
        }

        if ($baseTemplate) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($baseTemplate);
        } else {
            $spreadsheet = new Spreadsheet();
        }

        $sheet = $spreadsheet->getActiveSheet();
        if ($template === 'fabric' && $baseTemplate) {
            $sheet = $spreadsheet->getSheetByName('Ürünlerinizi Burada Listeleyin') ?: $spreadsheet->getActiveSheet();
        } elseif ($template === 'home_textile' && $baseTemplate) {
             $sheet = $spreadsheet->getSheet(0); 
        }

        // Dynamically discover columns from the first row of the template
        $columns = [];
        $maxCol = $sheet->getHighestColumn();
        $headerRow = $sheet->rangeToArray('A1:' . $maxCol . '1')[0];
        foreach ($headerRow as $idx => $label) {
            if (!empty($label)) {
                $columns[$idx] = trim($label);
            }
        }

        // If no columns found (empty file), use defaults
        if (empty($columns)) {
            $columns = $this->getTrendyolColumns($template);
            $colIdx = 0;
            foreach ($columns as $label) {
                $sheet->setCellValueByColumnAndRow($colIdx + 1, 1, $label);
                $columns[$colIdx] = $label;
                $colIdx++;
            }
        }

        $rowIdx = 2;
        foreach ($query->cursor() as $product) {
            $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();
            
            if ($variants->count() > 0) {
                foreach ($variants as $variant) {
                    $this->fillTrendyolRow($sheet, $rowIdx, $product, $variant, $template, $columns);
                    $rowIdx++;
                }
            } else {
                $this->fillTrendyolRow($sheet, $rowIdx, $product, null, $template, $columns);
                $rowIdx++;
            }
        }

        $tempId = uniqid('trendyol_export_', true);
        $path = "exports/{$tempId}.xlsx";
        $absolute = Storage::disk('local')->path($path);

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolute);

        return $absolute;
    }

    protected function getTrendyolColumns(string $template): array
    {
        if ($template === 'fabric') {
            return [
                'Barkod', 'Model Kodu', 'Marka', 'Kategori', 'Para Birimi', 'Ürün Adı', 'Ürün Açıklaması', 
                'Piyasa Satış Fiyatı (KDV Dahil)', 'Trendyol\'da Satılacak Fiyat (KDV Dahil)', 'Ürün Stok Adedi', 
                'Stok Kodu', 'KDV Oranı', 'ÖTV Oranı', 'Desi', 'Parti/Lot/SKT Bilgisi', 
                'Görsel 1', 'Görsel 2', 'Görsel 3', 'Görsel 4', 'Görsel 5', 'Görsel 6', 'Görsel 7', 'Görsel 8', 
                'Sevkiyat Süresi', 'Sevkiyat Tipi', 'Birincil İthalatçı Adı', 'Web Color', 'Menşei', 
                'Yıkama Talimatı', 'Üretici Adı', 'Materyal Bileşeni', 'Renk', 'Boyut/Ebat'
            ];
        }

        if ($template === 'home_textile') {
            return [
                'Barkod', 'Model Kodu', 'Marka', 'Kategori', 'Para Birimi', 'Ürün Adı', 'Ürün Açıklaması', 
                'Piyasa Satış Fiyatı (KDV Dahil)', 'Trendyol\'da Satılacak Fiyat (KDV Dahil)', 'Ürün Stok Adedi', 
                'Stok Kodu', 'KDV Oranı', 'ÖTV Oranı', 'Desi', 'Parti/Lot/SKT Bilgisi', 
                'Görsel 1', 'Görsel 2', 'Görsel 3', 'Görsel 4', 'Görsel 5', 'Görsel 6', 'Görsel 7', 'Görsel 8', 
                'Sevkiyat Süresi', 'Sevkiyat Tipi', 'Boyut/Ebat', 'İkincil İthalatçı Adres Bilgisi', 'Renk', 
                'Web Color', 'Üretici Adı', 'Materyal', 'Birincil İthalatçı Mail Adresi', 'Şekil', 'Özellik', 
                'Yıkama Talimatı', 'Üretici Mail Adresi', 'Üçüncül İthalatçı Mail Adresi', 'Üçüncül İthalatçı Adres Bilgisi', 
                'Paket İçeriği', 'Birincil İthalatçı Adı', 'Birincil İthalatçı Adres Bilgisi', 'İkincil İthalatçı Adı', 
                'Üretici Adres Bilgisi', 'İkincil İthalatçı Mail Adresi', 'Menşei', 'Desen', 'Materyal Bileşeni', 'Üçüncül İthalatçı Adı'
            ];
        }

        return [
            'Barkod', 'Model Kodu', 'Marka', 'Kategori', 'Para Birimi', 'Ürün Adı', 'Ürün Açıklaması', 
            'Piyasa Satış Fiyatı (KDV Dahil)', 'Trendyol\'da Satılacak Fiyat (KDV Dahil)', 'Ürün Stok Adedi', 
            'Stok Kodu', 'KDV Oranı', 'Desi', 'Görsel 1', 'Sevkiyat Süresi'
        ];
    }

    protected function fillTrendyolRow($sheet, $rowIdx, $product, $variant, $template, $columns)
    {
        foreach ($columns as $idx => $label) {
            $val = '';
            $salePrice = $variant 
                ? ($variant->special_price->amount() > 0 ? $variant->special_price->amount() : $variant->price->amount())
                : ($product->special_price->amount() > 0 ? $product->special_price->amount() : $product->price->amount());
            
            $marketPrice = $variant ? $variant->price->amount() : $product->price->amount();
            $sku = $variant ? ($variant->sku ?: $product->sku) : $product->sku;

            switch ($label) {
                case 'Barkod': $val = $sku; break;
                case 'Model Kodu': $val = $product->sku ?: $product->id; break;
                case 'Stok Kodu': $val = $sku; break;
                case 'Marka': $val = optional($product->brand)->name ?: setting('store_name'); break;
                case 'Kategori': $val = $product->categories->first()?->name ?: 'Genel'; break;
                case 'Para Birimi': $val = 'TRY'; break;
                case 'Ürün Adı': 
                    $val = $variant ? $product->name . ' (' . $variant->name . ')' : $product->name;
                    break;
                case 'Ürün Açıklaması': $val = $product->description; break;
                case 'Piyasa Satış Fiyatı (KDV Dahil)': $val = (float) $marketPrice; break;
                case 'Trendyol\'da Satılacak Fiyat (KDV Dahil)':
                case 'Satış Fiyatı (KDV Dahil)': 
                    $val = (float) $salePrice; break;
                case 'Ürün Stok Adedi':
                case 'Stok Adedi': $val = (int) ($variant ? $variant->qty : $product->qty); break;
                case 'KDV Oranı': $val = (int) setting('product_feeds.google.default_vat_rate', 20); break;
                case 'Desi': $val = (float) ($product->weight ?? 1); break;
                case 'Sevkiyat Süresi': $val = '2'; break;
                case 'Sevkiyat Tipi': $val = 'Sendeo'; break;
                case 'ÖTV Oranı': $val = '0'; break;
                case 'Menşei': $val = 'Türkiye'; break;
                case 'Üretici Adı': $val = setting('store_name'); break;
                case 'Web Color':
                case 'Renk': 
                    $val = $variant ? $this->getVariantAttributeValue($variant, 'Renk') : $this->getProductAttributeValue($product, 'Renk');
                    break;
                case 'Boyut/Ebat':
                    $val = $variant ? $this->getVariantAttributeValue($variant, 'Boyut') : $this->getProductAttributeValue($product, 'Boyut');
                    if (empty($val)) $val = $variant ? $this->getVariantAttributeValue($variant, 'Ebat') : $this->getProductAttributeValue($product, 'Ebat');
                    break;
                case 'Materyal':
                case 'Materyal Bileşeni':
                    $val = $this->getProductAttributeValue($product, 'Materyal');
                    break;
                case 'Yıkama Talimatı': $val = $this->getProductAttributeValue($product, 'Yıkama'); break;
                case 'Şekil': $val = $this->getProductAttributeValue($product, 'Şekil'); break;
                case 'Desen': $val = $this->getProductAttributeValue($product, 'Desen'); break;
                case 'Paket İçeriği': $val = $this->getProductAttributeValue($product, 'Paket'); break;
                case 'Özellik': $val = $this->getProductAttributeValue($product, 'Özellik'); break;
            }

            // Handle Visuals
            if (strpos($label, 'Görsel ') === 0) {
                $num = (int) str_replace('Görsel ', '', $label);
                $images = $variant ? $this->getVariantImages($variant, $product) : $this->getProductImages($product);
                $val = $images->get($num - 1) ?: '';
            }

            $sheet->setCellValueByColumnAndRow($idx + 1, $rowIdx, $val);
        }
    }

    protected function getProductAttributeValue(Product $product, string $name): string
    {
        // Search in attributes
        $attr = $product->attributes->filter(function($a) use ($name) {
            return stripos($a->name, $name) !== false;
        })->first();
        
        if ($attr) {
            return $attr->values->pluck('name')->implode(', ');
        }

        return '';
    }

    protected function getVariantAttributeValue($variant, string $name): string
    {
        $labels = $variant->getVariationLabels();
        
        foreach ($labels as $varName => $value) {
            if (stripos($varName, $name) !== false) {
                return $value;
            }
        }

        // Fallback to searching variant name if it's descriptive
        if (stripos($variant->name, $name) !== false) {
           return $variant->name; 
        }
        
        return '';
    }

    public function exportHepsiburada(Builder $query): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [
            'StokKodu', 'Barkod', 'ÜrünAdı', 'Marka', 'Kategori', 'SatışFiyatı', 'ListeFiyatı', 'StokAdedi', 'KdvOranı', 'Desi', 'Görsel1', 'Açıklama'
        ];

        $col = 'A';
        foreach ($columns as $label) {
            $sheet->setCellValue($col . '1', $label);
            $col++;
        }

        $rowIdx = 2;
        foreach ($query->cursor() as $product) {
            $variants = $product->relationLoaded('variants') ? $product->variants : $product->variants()->get();

            if ($variants->count() > 0) {
                foreach ($variants as $variant) {
                    $sheet->setCellValue('A' . $rowIdx, $variant->sku ?: $product->sku);
                    $sheet->setCellValue('B' . $rowIdx, $variant->sku ?: $product->sku);
                    $sheet->setCellValue('C' . $rowIdx, $product->name . ' (' . $variant->name . ')');
                    $sheet->setCellValue('D' . $rowIdx, optional($product->brand)->name ?: setting('store_name'));
                    $sheet->setCellValue('E' . $rowIdx, $product->categories->first()?->name ?: 'Genel');
                    
                    $vSalePrice = $variant->special_price->amount() > 0 ? $variant->special_price->amount() : $variant->price->amount();
                    $sheet->setCellValue('F' . $rowIdx, (float) $vSalePrice);
                    $sheet->setCellValue('G' . $rowIdx, (float) $variant->price->amount());
                    $sheet->setCellValue('H' . $rowIdx, (int) $variant->qty);
                    $sheet->setCellValue('I' . $rowIdx, (int) setting('product_feeds.google.default_vat_rate', 20));
                    $sheet->setCellValue('J' . $rowIdx, (float) ($product->weight ?? 1));
                    
                    $vImages = $this->getVariantImages($variant, $product);
                    $sheet->setCellValue('K' . $rowIdx, $vImages->first() ?: '');
                    $sheet->setCellValue('L' . $rowIdx, $product->description);
                    $rowIdx++;
                }
            } else {
                $sheet->setCellValue('A' . $rowIdx, $product->sku);
                $sheet->setCellValue('B' . $rowIdx, $product->sku);
                $sheet->setCellValue('C' . $rowIdx, $product->name);
                $sheet->setCellValue('D' . $rowIdx, optional($product->brand)->name ?: setting('store_name'));
                $sheet->setCellValue('E' . $rowIdx, $product->categories->first()?->name ?: 'Genel');
                
                $salePrice = $product->special_price->amount() > 0 ? $product->special_price->amount() : $product->price->amount();
                $sheet->setCellValue('F' . $rowIdx, (float) $salePrice);
                $sheet->setCellValue('G' . $rowIdx, (float) $product->price->amount());
                $sheet->setCellValue('H' . $rowIdx, (int) $product->qty);
                $sheet->setCellValue('I' . $rowIdx, (int) setting('product_feeds.google.default_vat_rate', 20));
                $sheet->setCellValue('J' . $rowIdx, (float) ($product->weight ?? 1));
                
                $pImages = $this->getProductImages($product);
                $sheet->setCellValue('K' . $rowIdx, $pImages->first() ?: '');
                $sheet->setCellValue('L' . $rowIdx, $product->description);
                $rowIdx++;
            }
        }

        $tempId = uniqid('hepsiburada_export_', true);
        $path = "exports/{$tempId}.xlsx";
        $absolute = Storage::disk('local')->path($path);

        $writer = new Xlsx($spreadsheet);
        $writer->save($absolute);

        return $absolute;
    }
}
