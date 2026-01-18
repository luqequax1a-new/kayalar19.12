<?php

return [
    'attributes' => [
        'attribute_set_id' => 'Attribute Set',
        'name' => 'Name',
        'categories' => 'Categories',
        'slug' => 'URL',
        'is_filterable' => 'Filterable',
        'filterable_type' => 'Filter Type',
    ],
    'filterable_types' => [
        'checkbox' => 'Checkbox',
        'radio' => 'Radio List',
        'dropdown' => 'Dropdown Menu',
        'range' => 'Numerical Range',
        'color' => 'Color Swatch',
    ],
    'attribute_sets' => [
        'name' => 'Name',
    ],
    'product_attributes' => [
        'attributes.*.attribute_id' => 'Attribute',
        'attributes.*.values' => 'Values',
    ],
];
