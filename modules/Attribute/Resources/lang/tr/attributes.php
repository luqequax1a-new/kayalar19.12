<?php

return [
    'attributes' => [
        'attribute_set_id' => 'Attribute Set',
        'name' => 'Name',
        'categories' => 'Categories',
        'slug' => 'URL',
        'is_filterable' => 'Filtrelenebilir',
        'filterable_type' => 'Filtre Tipi',
    ],
    'filterable_types' => [
        'checkbox' => 'Onay Kutusu (Checkbox)',
        'radio' => 'Tekli Seçim (Radio)',
        'dropdown' => 'Açılır Menü (Dropdown)',
        'range' => 'Sayısal Aralık (Range)',
        'color' => 'Renk Seçimi (Swatch)',
    ],
    'attribute_sets' => [
        'name' => 'Name',
    ],
    'product_attributes' => [
        'attributes.*.attribute_id' => 'Attribute',
        'attributes.*.values' => 'Values',
    ],
];
