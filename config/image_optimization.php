<?php

return [

    'master' => [
        'max_width' => 1800,
        'max_height' => 1800,
        'jpeg_quality' => 92,
        'png_compression_level' => 7,
        'convert_opaque_png_to_jpeg' => true,
        'jpeg_background_color' => [255, 255, 255],
        'fix_orientation' => true,
    ],

    'ikas_pool' => [
        'widths' => [
            16, 32, 48, 64, 96, 128, 180, 256, 360, 384, 540, 720, 900, 1080, 1296, 1512, 1728, 1950, 2560, 3840,
        ],
        'legacy_widths' => [
            40, 80, 260, 400, 520, 780, 800, 1000, 1200, 1800,
        ],
        'srcset_no_exists_check' => true,
        'jpeg_quality' => 92,
        'webp_quality' => 90,
        'avif_quality' => 88,
        'enable_avif' => true,
    ],

    'lqip' => [
        'width' => 32,
        'blur' => true,
    ],

    'variants' => [
        'widths' => [
            'thumb' => 180,
            'thumb_2x' => 360,
            'card' => 540,
            'card_2x' => 1080,
            'card_3x' => 1512,
            'grid' => 540,
            'grid_2x' => 1080,
            'grid_3x' => 1512,
            'detail' => 1080,
            'detail_2x' => 1512,
        ],
        'jpeg_quality' => 92,
        'webp_quality' => 90,
        'avif_quality' => 88,
        'enable_avif' => true,
        'thumbs_require_imagick' => true,
    ],

    'fast_listing' => [
        'width' => 540,
        'webp_quality' => 90,
        'avif_quality' => 88,
        'enable_avif' => true,
    ],

];
