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

    protected array $poolWidths = [];

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
        $pool = array_merge(
            (array) config('image_optimization.ikas_pool.widths', []),
            (array) config('image_optimization.ikas_pool.legacy_widths', [])
        );
        $this->poolWidths = array_values(array_unique(array_filter(array_map('intval', $pool))));

        $widths = (array) config('image_optimization.variants.widths', []);

        $thumbWidth = (int) ($widths['thumb'] ?? 80);
        $thumb2xWidth = (int) ($widths['thumb_2x'] ?? 0);
        $cardWidth = (int) ($widths['card'] ?? 260);
        $card2xWidth = (int) ($widths['card_2x'] ?? 520);
        $card3xWidth = (int) ($widths['card_3x'] ?? 780);
        $gridWidth = (int) ($widths['grid'] ?? 400);
        $grid2xWidth = (int) ($widths['grid_2x'] ?? 0);
        $grid3xWidth = (int) ($widths['grid_3x'] ?? 0);
        $detailWidth = (int) ($widths['detail'] ?? 1000);
        $detail2xWidth = (int) ($widths['detail_2x'] ?? 0);

        $this->presets = [
            'card' => [$cardWidth],
            'card_retina' => array_values(array_filter([$card2xWidth, $card3xWidth])),
            'grid' => array_values(array_filter([$gridWidth, $grid2xWidth, $grid3xWidth])),
            'detail' => array_values(array_filter([$detailWidth, $detail2xWidth])),
        ];

        $this->thumbs = array_values(array_filter([$thumbWidth, $thumb2xWidth]));

        $this->jpegQuality = (int) config('image_optimization.variants.jpeg_quality', 88);
        $this->webpQuality = (int) config('image_optimization.ikas_pool.webp_quality', config('image_optimization.variants.webp_quality', 85));
        $this->avifQuality = (int) config('image_optimization.ikas_pool.avif_quality', config('image_optimization.variants.avif_quality', 85));
        $this->enableAvif = (bool) config('image_optimization.ikas_pool.enable_avif', config('image_optimization.variants.enable_avif', true));

        $this->fastListingWidth = (int) config('image_optimization.fast_listing.width', $gridWidth);
        $this->fastWebpQuality = (int) config('image_optimization.fast_listing.webp_quality', 65);
        $this->fastAvifQuality = (int) config('image_optimization.fast_listing.avif_quality', 40);
        $this->enableFastAvif = (bool) config('image_optimization.fast_listing.enable_avif', true);
    }

    public function generateVariants(MediaFile $file, bool $force = false): void
    {
        if (!$file->isImage()) return;

        $raw = $file->getRawOriginal('path');
        $disk = $file->disk;
        $source = $file->realPath();
        if (!$source || !is_file($source)) return;

        $hasImagick = class_exists('\\Imagick');
        $thumbsRequireImagick = (bool) config('image_optimization.variants.thumbs_require_imagick', false);

        $pool = $this->poolWidths;
        if (empty($pool)) {
            $pool = array_merge(...array_values($this->presets));
            $pool = array_merge($pool, $this->thumbs, [$this->fastListingWidth]);
            $pool = array_values(array_unique($pool));
            $pool = array_values(array_filter(array_map('intval', $pool)));
        }

        if ($hasImagick) {
            foreach ($pool as $w) {
                if ($w <= 0) continue;
                $this->writeVariantWithImagick($disk, $raw, $source, $w, 'jpg', '', null, $force);
                $this->writeVariantWithImagick($disk, $raw, $source, $w, 'webp', '', null, $force);
                if ($this->enableAvif) {
                    $this->writeVariantWithImagick($disk, $raw, $source, $w, 'avif', '', null, $force);
                }
            }

            // Fast listing variants (separate filenames) for LCP candidates.
            $w = (int) $this->fastListingWidth;
            if ($w > 0) {
                $this->writeVariantWithImagick($disk, $raw, $source, $w, 'webp', 'fast', $this->fastWebpQuality, $force);
                if ($this->enableAvif && $this->enableFastAvif) {
                    $this->writeVariantWithImagick($disk, $raw, $source, $w, 'avif', 'fast', $this->fastAvifQuality, $force);
                }
            }

            return;
        }

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

        foreach ($pool as $w) {
            if ($w <= 0) continue;
            $this->writeVariant($disk, $raw, $img, $ext, $w, 'jpg', '', null, $force);
            $this->writeVariant($disk, $raw, $img, $ext, $w, 'webp', '', null, $force);
            if ($this->enableAvif) {
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'avif', '', null, $force);
            }
        }

        if ($thumbsRequireImagick) {
            // no-op: thumbs are part of the pool; when Imagick is required but missing we only do GD fallback above.
        }

        // Fast listing variants (separate filenames) for LCP candidates.
        $w = (int) $this->fastListingWidth;
        if ($w > 0) {
            $this->writeVariant($disk, $raw, $img, $ext, $w, 'webp', 'fast', $this->fastWebpQuality, $force);
            if ($this->enableAvif && $this->enableFastAvif) {
                $this->writeVariant($disk, $raw, $img, $ext, $w, 'avif', 'fast', $this->fastAvifQuality, $force);
            }
        }

        imagedestroy($img);
    }

    protected function writeVariantWithImagick(
        string $disk,
        string $rawPath,
        string $sourcePath,
        int $width,
        string $format,
        string $suffix = '',
        ?int $qualityOverride = null,
        bool $force = false
    ): void
    {
        if (!class_exists('\\Imagick')) return;

        $targetRel = $this->buildVariantRelativePath($rawPath, $width, $format, $suffix);
        if (!$force && Storage::disk($disk)->exists($targetRel)) return;
        if ($force && Storage::disk($disk)->exists($targetRel)) {
            Storage::disk($disk)->delete($targetRel);
        }

        try {
            $im = new \Imagick($sourcePath);
            $im->autoOrient();

            $srcW = (int) $im->getImageWidth();
            if ($srcW > 0 && $width > $srcW) {
                $width = $srcW;
            }

            $im->resizeImage($width, 0, \Imagick::FILTER_LANCZOS, 1);

            $finalW = (int) $im->getImageWidth();
            if ($finalW > 0 && $finalW <= 600) {
                $im->unsharpMaskImage(0.25, 0.25, 0.5, 0.02);
            }

            $fmt = strtolower($format);
            $jpegQ = $qualityOverride ?? $this->jpegQuality;
            $webpQ = $qualityOverride ?? $this->webpQuality;
            $avifQ = $qualityOverride ?? $this->avifQuality;

            if (method_exists($im, 'stripImage')) {
                $im->stripImage();
            }

            if (in_array($fmt, ['jpg', 'jpeg'], true)) {
                $im->setImageFormat('jpeg');
                if (method_exists($im, 'setInterlaceScheme')) {
                    $im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
                }
                if (method_exists($im, 'setSamplingFactors')) {
                    $im->setSamplingFactors(['1x1', '1x1', '1x1']);
                }
                $im->setImageCompressionQuality(max(0, min(100, $jpegQ)));
            } elseif ($fmt === 'webp') {
                $im->setImageFormat('webp');
                $im->setOption('webp:method', '6');
                $im->setImageCompressionQuality(max(0, min(100, $webpQ)));
            } elseif ($fmt === 'avif') {
                $im->setImageFormat('avif');
                $im->setOption('avif:speed', '6');
                $im->setImageCompressionQuality(max(0, min(100, $avifQ)));
            } else {
                $im->setImageFormat($fmt);
            }

            $blob = $im->getImageBlob();
            $im->clear();
            $im->destroy();

            if (is_string($blob) && strlen($blob) > 0) {
                Storage::disk($disk)->put($targetRel, $blob);
            }
        } catch (\Throwable $e) {
            return;
        }
    }

    protected function writeVariant(
        string $disk,
        string $rawPath,
        $img,
        string $originalExt,
        int $width,
        ?string $format,
        string $suffix = '',
        ?int $qualityOverride = null,
        bool $force = false
    ): void
    {
        $srcW = imagesx($img);
        if ($srcW > 0 && $width > $srcW) {
            $width = $srcW;
        }

        $targetRel = $this->buildVariantRelativePath($rawPath, $width, $format ?? $originalExt, $suffix);
        if (!$force && Storage::disk($disk)->exists($targetRel)) return;
        if ($force && Storage::disk($disk)->exists($targetRel)) {
            Storage::disk($disk)->delete($targetRel);
        }

        $scaled = null;
        if (function_exists('imagescale')) {
            $scaled = imagescale($img, $width, -1, defined('IMG_BICUBIC_FIXED') ? IMG_BICUBIC_FIXED : IMG_BILINEAR_FIXED);
        }
        if ($scaled === false) return;

        if ($scaled === null) {
            $srcW = imagesx($img);
            $srcH = imagesy($img);
            if ($srcW <= 0 || $srcH <= 0) return;
            $dstW = max(1, $width);
            $dstH = max(1, (int) round(($srcH / $srcW) * $dstW));
            $scaled = imagecreatetruecolor($dstW, $dstH);
            if ($scaled === false) return;
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            if (!imagecopyresampled($scaled, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH)) {
                imagedestroy($scaled);
                return;
            }
        }

        $finalW = imagesx($scaled);
        if ($finalW > 0 && $finalW <= 600 && function_exists('imageconvolution')) {
            @imageconvolution($scaled, [[0, -1, 0], [-1, 5, -1], [0, -1, 0]], 1, 0);
        }

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
