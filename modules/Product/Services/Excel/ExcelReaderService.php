<?php

namespace Modules\Product\Services\Excel;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReaderService
{
    public function storeUploadedFile(UploadedFile $file): string
    {
        $tempId = uniqid('products_excel_', true);
        $extension = $file->getClientOriginalExtension();
        $path = "tmp/{$tempId}.{$extension}";
        Storage::disk('local')->putFileAs('tmp', $file, "{$tempId}.{$extension}");

        Storage::disk('local')->put("tmp/{$tempId}.meta.json", json_encode([
            'extension' => $extension,
        ]));

        return $tempId;
    }

    protected function getMeta(string $tempId): array
    {
        $metaPath = "tmp/{$tempId}.meta.json";
        if (!Storage::disk('local')->exists($metaPath)) {
            return ['extension' => 'xlsx'];
        }

        return json_decode(Storage::disk('local')->get($metaPath), true) ?: ['extension' => 'xlsx'];
    }

    protected function getFilePath(string $tempId): string
    {
        $meta = $this->getMeta($tempId);
        $extension = $meta['extension'] ?? 'xlsx';
        return Storage::disk('local')->path("tmp/{$tempId}.{$extension}");
    }

    public function getHeaders(string $tempId): array
    {
        $path = $this->getFilePath($tempId);

        if (!is_readable($path)) {
            return [];
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $headers = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];

        return array_values(array_filter(array_map('trim', $headers), fn ($h) => $h !== ''));
    }

    public function readRows(string $tempId): \Generator
    {
        $path = $this->getFilePath($tempId);

        if (!is_readable($path)) {
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headers = $sheet->rangeToArray('A1:' . $highestColumn . '1', NULL, TRUE, FALSE)[0];
        $headers = array_values(array_map('trim', $headers));

        for ($row = 2; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE)[0];
            $assoc = [];
            foreach ($headers as $index => $name) {
                if ($name === '') continue;
                $assoc[$name] = $rowData[$index] ?? null;
            }
            yield $assoc;
        }
    }
}
