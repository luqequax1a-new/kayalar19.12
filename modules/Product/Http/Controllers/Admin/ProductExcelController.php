<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Product\Entities\Product;
use Modules\Product\Services\Excel\ExcelExportService;
use Modules\Product\Services\Excel\ExcelReaderService;
use Modules\Product\Services\Csv\CsvBulkUpdateService;

class ProductExcelController
{
    public function export(Request $request, ExcelExportService $exportService): BinaryFileResponse
    {
        $query = $this->buildBaseQuery($request);
        $filePath = $exportService->exportProducts($query);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportTrendyol(Request $request, ExcelExportService $exportService): BinaryFileResponse
    {
        $query = $this->buildBaseQuery($request);
        $filePath = $exportService->exportTrendyol($query, $request->input('template', 'general'));

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function exportHepsiburada(Request $request, ExcelExportService $exportService): BinaryFileResponse
    {
        $query = $this->buildBaseQuery($request);
        $filePath = $exportService->exportHepsiburada($query);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function import(Request $request, ExcelReaderService $reader, CsvBulkUpdateService $bulkUpdate)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
            'mode' => ['required', 'in:create,update'],
            'identifier' => ['required', 'in:id,sku'],
        ]);

        $tempId = $reader->storeUploadedFile($request->file('file'));
        $headers = $reader->getHeaders($tempId);

        $total = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rows = [];

        $mapping = $this->autoMapHeaders($headers);

        foreach ($reader->readRows($tempId) as $row) {
            $total++;
            $data = [];
            foreach ($mapping as $csvColumn => $field) {
                $data[$field] = $row[$csvColumn] ?? null;
            }

            try {
                if ($request->mode === 'create') {
                    $bulkUpdate->handleRow($data, 'create', $request->identifier);
                    $created++;
                    $rows[] = [
                        'row' => $total,
                        'action' => 'create',
                        'identifier' => $data['sku'] ?? ($data['id'] ?? ''),
                        'message' => null,
                    ];
                    continue;
                }

                $identifier = $request->identifier;
                $product = null;
                if ($identifier === 'id' && !empty($data['id'])) {
                    $product = Product::query()->withoutGlobalScope('active')->find((int) $data['id']);
                } elseif ($identifier === 'sku' && !empty($data['sku'])) {
                    $product = Product::query()->withoutGlobalScope('active')->where('sku', $data['sku'])->first();
                }

                if (!$product) {
                    $skipped++;
                    $msg = 'Ürün bulunamadı';
                    $rows[] = [
                        'row' => $total,
                        'action' => 'skipped',
                        'identifier' => $data[$identifier] ?? '',
                        'message' => $msg,
                    ];
                    continue;
                }

                $bulkUpdate->handleRow($data, 'update', $identifier);
                $updated++;
                $rows[] = [
                    'row' => $total,
                    'action' => 'update',
                    'identifier' => $data[$identifier] ?? '',
                    'message' => null,
                ];
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = ['row' => $total, 'message' => $e->getMessage()];
                $rows[] = [
                    'row' => $total,
                    'action' => 'error',
                    'identifier' => $data['sku'] ?? ($data['id'] ?? ''),
                    'message' => $e->getMessage(),
                ];
            }
        }

        return view('product::admin.products.simple_csv_import_result', [
            'total' => $total,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
            'rows' => $rows,
        ]);
    }

    protected function buildBaseQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()
            ->withoutGlobalScope('active')
            ->with(['translations', 'categories', 'brand', 'tags', 'saleUnit', 'productMedia', 'files', 'attributes.values', 'variants'])
            ->when($request->has('brand_id') && $request->brand_id !== null && $request->brand_id !== '', function ($q) use ($request) {
                $q->where('brand_id', (int) $request->brand_id);
            })
            ->when($request->has('category_id') && $request->category_id !== null && $request->category_id !== '', function ($q) use ($request) {
                $categoryId = (int) $request->category_id;
                $q->where(function ($sub) use ($categoryId) {
                    $sub->where('primary_category_id', $categoryId)
                        ->orWhereHas('categories', function ($cat) use ($categoryId) {
                            $cat->where('categories.id', $categoryId);
                        });
                });
            })
            ->when($request->input('search'), function ($q, $search) {
                $q->whereHas('translations', function ($t) use ($search) {
                    $t->where('name', 'like', '%' . $search . '%');
                });
            });
    }

    protected function autoMapHeaders(array $headers): array
    {
        $mapping = [];
        foreach ($headers as $header) {
            $key = strtolower(trim($header));
            
            // System fields
            if (in_array($key, ['id', 'ürün id', 'product id'])) $mapping[$header] = 'id';
            elseif (in_array($key, ['sku', 'stok kodu', 'barkod', 'barcode', 'stokkodu', 'model kodu'])) $mapping[$header] = 'sku';
            elseif (in_array($key, ['name', 'ürün adı', 'urun adı', 'başlık', 'ürünadı'])) $mapping[$header] = 'name';
            elseif (in_array($key, ['description', 'açıklama', 'ürün açıklaması', 'urun açıklaması'])) $mapping[$header] = 'description';
            elseif (in_array($key, ['short_description', 'kısa açıklama'])) $mapping[$header] = 'short_description';
            
            // Prices
            elseif (in_array($key, ['price', 'fiyat', 'liste fiyatı', 'piyasa satış fiyatı (kdv dahil)', 'listefiyatı'])) $mapping[$header] = 'price';
            elseif (in_array($key, ['special_price', 'indirimli fiyat', 'satış fiyatı', 'satış fiyatı (kdv dahil)', 'satışfiyatı'])) $mapping[$header] = 'special_price';
            
            // Inventory
            elseif (in_array($key, ['qty', 'stok', 'miktar', 'stok adedi', 'stokadedi'])) $mapping[$header] = 'qty';
            elseif (in_array($key, ['manage_stock', 'stok takibi'])) $mapping[$header] = 'manage_stock';
            elseif (in_array($key, ['in_stock', 'stok durumu'])) $mapping[$header] = 'in_stock';
            
            // Meta
            elseif (in_array($key, ['slug', 'url'])) $mapping[$header] = 'slug';
            elseif (in_array($key, ['category_ids', 'kategoriler', 'kategori'])) $mapping[$header] = 'category_ids';
            elseif (in_array($key, ['brand_id', 'marka'])) $mapping[$header] = 'brand_id';
            elseif (in_array($key, ['images', 'görseller', 'görsel 1', 'görsel1'])) $mapping[$header] = 'images';
        }
        return $mapping;
    }
}
