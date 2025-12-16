<?php

namespace Modules\Media\Services;

use Modules\Media\Entities\File as MediaFile;
use Illuminate\Support\Facades\Storage;

class ResponsiveImageGenerator
{
    protected array $presets = [
        'grid' => [400],
        'card' => [260],
        'card_retina' => [520, 780],
        'detail' => [1000],
    ];

    protected array $thumbs = [80];

    protected int $jpegQuality = 88;

    protected int $webpQuality = 85;

    protected int $avifQuality = 85;

    protected bool $enableAvif = true;

    protected int $fastListingWidth = 400;

    protected int $fastWebpQuality = 65;

    protected int $fastAvifQuality = 40;

    protected bool $enableFastAvif = true;


    public function __construct()
    {
        $widths = (array) config('image_optimization.variants.widths', []);

        $thumbWidth = (int) ($widths['thumb'] ?? 80);
        $cardWidth = (int) ($widths['card'] ?? 260);
        $card2xWidth = (int) ($widths['card_2x'] ?? 520);
        $card3xWidth = (int) ($widths['card_3x'] ?? 780);
        $gridWidth = (int) ($widths['grid'] ?? 400);
        $detailWidth = (int) ($widths['detail'] ?? 1000);

        $this->presets = [
            'card' => [$cardWidth],
            'card_retina' => array_values(array_filter([$card2xWidth, $card3xWidth])),
            'grid' => [$gridWidth],
            'detail' => [$detailWidth],
        ];

        $this->thumbs = [$thumbWidth];

        $this->jpegQuality = (int) config('image_optimization.variants.jpeg_quality', 88);
        $this->webpQuality = (int) config('image_optimization.variants.webp_quality', 85);
        $this->avifQuality = (int) config('image_optimization.variants.avif_quality', 85);
        $this->enableAvif = (bool) config('image_optimization.variants.enable_avif', true);

        $this->fastListingWidth = (int) config('image_optimization.fast_listing.width', $gridWidth);
        $this->fastWebpQuality = (int) config('image_optimization.fast_listing.webp_quality', 65);
        $this->fastAvifQuality = (int) config('image_optimization.fast_listing.avif_quality', 40);
        $this->enableFastAvif = (bool) config('image_optimization.fast_listing.enable_avif', true);
    }

    public function generateVariants(MediaFile $file): void
    {
        if (!$file->isImage()) return;

        $raw = $file->getRawOriginal('path');
        $disk = $file->disk;
        $source = $file->realPath();
        if (!$source || !is_file($source)) return;

        $binary = @file_get_contents($source);
        $img = ($binary !== false) ? @imagecreatefromstring($binary) : false;

        if ($img === false && class_exists('\\Imagick')) {
            try {
                $im = new \Imagick($source);
                $im->setImageFormat('png');
                $pngData = $im->getImageBlob();
                $im->clear();
                $im->destroy();
                if (is_string($pngData) && strlen($pngData) > 0) {
                    $img = @imagecreatefromstring($pngData);
                }
            } catch (\Throwable $e) {
                $img = false;
            }
        }

        if ($img === false) return;

        $ext = strtolower($file->extension ?: pathinfo($raw, PATHINFO_EXTENSION));

        foreach ($this->presets as $sizes) {
            foreach ($sizes as $w) {
                $this->writeVariant($disk, $raw, $img, $ext, $w, null);
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'webp');
                if ($this->enableAvif) {
                    $this->writeVariant($disk, $raw, $img, $ext, $w, 'avif');
                }
            }
        }

        foreach ($this->thumbs as $tw) {
            $this->writeVariant($disk, $raw, $img, $ext, $tw, null);
            $this->writeVariant($disk, $raw, $img, $ext, $tw, 'webp');
            if ($this->enableAvif) {
                $this->writeVariant($disk, $raw, $img, $ext, $tw, 'avif');
            }
        }

        // Fast listing variants (separate filenames) for LCP candidates.
        $w = (int) $this->fastListingWidth;
        if ($w > 0) {
            $this->writeVariant($disk, $raw, $img, $ext, $w, 'webp', 'fast', $this->fastWebpQuality);
            if ($this->enableAvif && $this->enableFastAvif) {
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'avif', 'fast', $this->fastAvifQuality);
            }
        }

        imagedestroy($img);
    }

    protected function writeVariant(
        string $disk,
        string $rawPath,
        $img,
        string $originalExt,
        int $width,
        ?string $format,
        string $suffix = '',
        ?int $qualityOverride = null
    ): void
    {
        $targetRel = $this->buildVariantRelativePath($rawPath, $width, $format ?? $originalExt, $suffix);
        if (Storage::disk($disk)->exists($targetRel)) return;

        $scaled = imagescale($img, $width);
        if ($scaled === false) return;

        ob_start();
        $ok = false;
        $fmt = strtolower($format ?? $originalExt);
        $jpegQ = $qualityOverride ?? $this->jpegQuality;
        $webpQ = $qualityOverride ?? $this->webpQuality;
        $avifQ = $qualityOverride ?? $this->avifQuality;

        if (in_array($fmt, ['jpg', 'jpeg'])) {
            $ok = imagejpeg($scaled, null, max(0, min(100, $jpegQ)));
            $data = ob_get_clean();
        } elseif ($fmt === 'png') {
            $ok = imagepng($scaled, null, 6);
            $data = ob_get_clean();
        } elseif ($fmt === 'webp') {
            if (function_exists('imagewebp')) {
                $ok = imagewebp($scaled, null, max(0, min(100, $webpQ)));
                $data = ob_get_clean();
            } else {
                $data = $this->encodeWithImagick($img, $width, 'webp', $webpQ, [
                    'strip' => true,
                    'webp_method' => 6,
                ]);
                $ok = is_string($data) && strlen($data) > 0;
                ob_end_clean();
            }
        } elseif ($fmt === 'avif') {
            if (function_exists('imageavif')) {
                $ok = imageavif($scaled, null, max(0, min(100, $avifQ)));
                $data = ob_get_clean();
            } else {
                $data = $this->encodeWithImagick($img, $width, 'avif', $avifQ, [
                    'strip' => true,
                    'avif_speed' => 8,
                ]);
                $ok = is_string($data) && strlen($data) > 0;
                ob_end_clean();
            }
        } else {
            $ok = imagejpeg($scaled, null, max(0, min(100, $jpegQ)));
            $data = ob_get_clean();
        }

        imagedestroy($scaled);

        if ($ok && is_string($data) && strlen($data) > 0) {
            Storage::disk($disk)->put($targetRel, $data);
        }
    }

    protected function buildVariantRelativePath(string $rawPath, int $width, string $format, string $suffix = ''): string
    {
        $dir = trim(dirname($rawPath), '/');
        $name = pathinfo($rawPath, PATHINFO_FILENAME);
        $suffixPart = trim((string) $suffix);
        $suffixPart = $suffixPart !== '' ? '-' . $suffixPart : '';
        return ($dir ? $dir.'/' : '').$name.$suffixPart.'-'.$width.'w'.'.'.strtolower($format);
    }

    protected function encodeWithImagick($gdImage, int $width, string $format, int $quality, array $options = []): ?string
    {
        if (!class_exists('\Imagick')) return null;
        $tmp = tempnam(sys_get_temp_dir(), 'img');
        if (!$tmp) return null;
        ob_start();
        imagepng($gdImage, null, 0);
        $pngData = ob_get_clean();
        if (!is_string($pngData) || strlen($pngData) === 0) {
            @unlink($tmp);
            return null;
        }
        file_put_contents($tmp, $pngData);
        try {
            $imagick = new \Imagick($tmp);
            $imagick->setImageFormat(strtolower($format));
            if (method_exists($imagick, 'resizeImage')) {
                $imagick->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);
            }

            if (!empty($options['strip']) && method_exists($imagick, 'stripImage')) {
                $imagick->stripImage();
            }

            if (strtolower($format) === 'webp') {
                $imagick->setImageCompressionQuality($quality);
                if (method_exists($imagick, 'setOption') && isset($options['webp_method'])) {
                    $imagick->setOption('webp:method', (string) $options['webp_method']);
                }
            }
            if (strtolower($format) === 'avif') {
                if (method_exists($imagick, 'setOption')) {
                    $imagick->setOption('avif:quality', (string) $quality);
                    if (isset($options['avif_speed'])) {
                        $imagick->setOption('avif:speed', (string) $options['avif_speed']);
                    }
                }
            }
            $out = $imagick->getImagesBlob();
            $imagick->clear();
            $imagick->destroy();
            @unlink($tmp);
            return $out;
        } catch (\Throwable $e) {
            @unlink($tmp);
            return null;
        }
    }
}
