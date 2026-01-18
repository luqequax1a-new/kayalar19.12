<?php

use Illuminate\Support\Facades\Storage;

function media_variant_url($file, int $width, string $format = null): ?string
{
    static $urlCache = [];

    $raw = null;
    $disk = null;
    $cacheKey = '';

    if ($file instanceof \Modules\Media\Entities\File) {
        $raw = $file->getRawOriginal('path');
        $disk = $file->disk ?: 'public';
        $cacheKey = "file_{$file->id}_{$width}_{$format}";
    } elseif (is_array($file) && isset($file['path'])) {
        $url = $file['path'];
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
        $cacheKey = "path_" . md5($url) . "_{$width}_{$format}";
    } elseif (is_object($file) && isset($file->path)) {
        $url = $file->path;
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
        $cacheKey = "path_" . md5($url) . "_{$width}_{$format}";
    }

    if (!$raw) return is_array($file) && isset($file['path']) ? $file['path'] : (string) ($file->path ?? '');

    if (isset($urlCache[$cacheKey])) {
        return $urlCache[$cacheKey];
    }

    $raw = str_replace('\\', '/', $raw);
    $raw = str_starts_with($raw, 'public/') ? substr($raw, 7) : $raw;

    if (trim(dirname($raw), '/') === '.') {
        $candidate = 'media/' . ltrim($raw, '/');
        if (Storage::disk($disk)->exists($candidate)) {
            $raw = $candidate;
        }
    }

    $name = pathinfo($raw, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($raw, PATHINFO_EXTENSION));
    $fmt = strtolower($format ?: $ext);
    $dir = trim(dirname($raw), '/');
    $variantRel = ($dir ? $dir.'/' : '').$name.'-'.$width.'w'.'.'.$fmt;

    $result = null;
    if (Storage::disk($disk)->exists($variantRel)) {
        $result = Storage::disk($disk)->url($variantRel);
    } elseif ($disk !== 'public' && Storage::disk('public')->exists($variantRel)) {
        $result = Storage::disk('public')->url($variantRel);
    } elseif ($format === null) {
        $result = Storage::disk($disk)->url($raw);
    }

    if ($cacheKey) {
        $urlCache[$cacheKey] = $result;
    }

    return $result;
}

function media_variant_url_no_check($file, int $width, string $format): ?string
{
    $raw = null;
    $disk = null;
    if ($file instanceof \Modules\Media\Entities\File) {
        $raw = $file->getRawOriginal('path');
        $disk = $file->disk ?: 'public';
    } elseif (is_array($file) && isset($file['path'])) {
        $url = $file['path'];
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
    } elseif (is_object($file) && isset($file->path)) {
        $url = $file->path;
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
    }

    if (!$raw) return null;

    $raw = str_replace('\\', '/', $raw);
    $raw = str_starts_with($raw, 'public/') ? substr($raw, 7) : $raw;

    if (trim(dirname($raw), '/') === '.') {
        $candidate = 'media/' . ltrim($raw, '/');
        $raw = $candidate;
    }

    $name = pathinfo($raw, PATHINFO_FILENAME);
    $dir = trim(dirname($raw), '/');
    $fmt = strtolower($format);
    $variantRel = ($dir ? $dir.'/' : '').$name.'-'.$width.'w'.'.'.$fmt;

    return Storage::disk($disk)->url($variantRel);
}

function media_variant_url_with_suffix($file, int $width, string $format = null, string $suffix = ''): ?string
{
    $raw = null;
    $disk = null;
    if ($file instanceof \Modules\Media\Entities\File) {
        $raw = $file->getRawOriginal('path');
        $disk = $file->disk ?: 'public';
    } elseif (is_array($file) && isset($file['path'])) {
        $url = $file['path'];
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
    } elseif (is_object($file) && isset($file->path)) {
        $url = $file->path;
        $base = Storage::disk(config('filesystems.default'))->url('');
        $raw = ltrim(str_replace($base, '', $url), '/');
        $disk = config('filesystems.default');
    }

    if (!$raw) return is_array($file) && isset($file['path']) ? $file['path'] : (string) ($file->path ?? '');

    $raw = str_replace('\\', '/', $raw);
    $raw = str_starts_with($raw, 'public/') ? substr($raw, 7) : $raw;

    if (trim(dirname($raw), '/') === '.') {
        $candidate = 'media/' . ltrim($raw, '/');
        if (Storage::disk($disk)->exists($candidate)) {
            $raw = $candidate;
        }
    }

    $name = pathinfo($raw, PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($raw, PATHINFO_EXTENSION));
    $fmt = strtolower($format ?: $ext);
    $dir = trim(dirname($raw), '/');
    $suffixPart = trim((string) $suffix);
    $suffixPart = $suffixPart !== '' ? '-' . $suffixPart : '';
    $variantRel = ($dir ? $dir.'/' : '').$name.$suffixPart.'-'.$width.'w'.'.'.$fmt;

    if (Storage::disk($disk)->exists($variantRel)) {
        return Storage::disk($disk)->url($variantRel);
    }

    if ($disk !== 'public' && Storage::disk('public')->exists($variantRel)) {
        return Storage::disk('public')->url($variantRel);
    }

    if ($format === null) {
        return Storage::disk($disk)->url($raw);
    }

    return null;
}
