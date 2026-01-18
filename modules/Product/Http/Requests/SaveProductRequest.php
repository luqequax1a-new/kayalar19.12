<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Modules\Option\Entities\Option;
use Modules\Product\Entities\Product;
use Modules\Core\Http\Requests\Request;
use Modules\Variation\Entities\Variation;
use Modules\Product\Rules\DistinctProductVariationValueLabel;
use Modules\Unit\Entities\Unit;

class SaveProductRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'product::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            $this->getProductRules(),
            $this->getProductAttributeRules(),
            $this->getProductVariationsRules(),
            $this->getProductVariantsRules(),
            $this->getProductOptionsRules(),
            $this->getProductMediaRules(),
        );
    }

    public function prepareForValidation()
    {
        Log::debug('SaveProductRequest: prepareForValidation start', ['route' => optional($this->route())->getName()]);
        $data = $this->all();

        $normalize = function ($v) {
            if (is_string($v)) {
                $v = str_replace(',', '.', $v);
            }
            return $v;
        };

        foreach (['price', 'special_price', 'qty'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $normalize($data[$field]);
            }
        }

        if (isset($data['variants']) && is_array($data['variants'])) {
            foreach ($data['variants'] as $key => $variant) {
                foreach (['price', 'special_price', 'qty'] as $field) {
                    if (isset($variant[$field])) {
                        $data['variants'][$key][$field] = $normalize($variant[$field]);
                    }
                }
            }
            $data['has_active_variants'] = collect($data['variants'])
                ->filter(function ($v) {
                    return isset($v['is_active']) && (string)$v['is_active'] !== '0' && $v['is_active'] !== false;
                })
                ->isNotEmpty() ? 1 : 0;

            // Variants aktifken, ürün qty alanı boş ise request'ten tamamen kaldır
            // Böylece model update sırasında qty null'a set edilmez ve mevcut değer korunur.
            if (($data['has_active_variants'] ?? 0) === 1) {
                if (!array_key_exists('qty', $data) || $data['qty'] === null || $data['qty'] === '') {
                    unset($data['qty']);
                }
            }
        }

        if (isset($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $okey => $option) {
                if (isset($option['values']) && is_array($option['values'])) {
                    foreach ($option['values'] as $vkey => $value) {
                        if (isset($value['price'])) {
                            $data['options'][$okey]['values'][$vkey]['price'] = $normalize($value['price']);
                        }
                    }
                }
            }
        }

        $this->merge($data);
        Log::debug('SaveProductRequest: prepareForValidation merged', [
            'product' => [
                'price' => $data['price'] ?? null,
                'special_price' => $data['special_price'] ?? null,
                'qty' => $data['qty'] ?? null,
            ],
            'variants_count' => isset($data['variants']) && is_array($data['variants']) ? count($data['variants']) : 0,
            'has_active_variants' => $data['has_active_variants'] ?? 0,
        ]);
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('SaveProductRequest: validation failed', [
            'errors' => $validator->errors()->toArray(),
            'route' => optional($this->route())->getName(),
        ]);
        parent::failedValidation($validator);
    }


    public function getProductRules(): array
    {
        return array_merge(
            [
                'slug' => $this->getSlugRules(),
                'name' => 'required',
                'description' => 'required',
                'brand_id' => ['nullable', Rule::exists('brands', 'id')],
                'tax_class_id' => ['nullable', Rule::exists('tax_classes', 'id')],
                'sale_unit_id' => ['nullable', Rule::exists('units', 'id')],
                'primary_category_id' => ['nullable', Rule::exists('categories', 'id')],
                'price' => 'required_unless:has_active_variants,1|nullable|numeric|min:0|max:99999999999999',
                'special_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:99999999999999',
                    function ($attribute, $value, $fail) {
                        $type = $this->input('special_price_type');
                        if ($type === 'percent') {
                            return;
                        }
                        $price = $this->input('price');
                        if ($value === null || $value === '') {
                            return;
                        }
                        if (is_numeric($price) && is_numeric($value) && (float) $value > (float) $price) {
                            $fail('İndirimli fiyat satış fiyatından yüksek olamaz.');
                        }
                    },
                ],
                'special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
                'special_price_start' => 'nullable|date|before_or_equal:special_price_end',
                'special_price_end' => 'nullable|date|after_or_equal:special_price_start',
                'manage_stock' => 'required|boolean',
                'qty' => 'nullable|numeric|min:0',
                'in_stock' => 'required|boolean',
                'new_from' => 'nullable|date',
                'new_to' => 'nullable|date',
                'is_virtual' => 'required|boolean',
                'is_active' => 'required|boolean',
                'media' => 'nullable|array',
                'media.*' => 'integer|min:1',
                'redirect_type' => ['nullable', 'string', Rule::in(['404', '410', '301-category', '302-category', '301-product', '302-product'])],
                'redirect_target_id' => 'nullable|integer|min:1',
            ],
            $this->getInventoryRules()
        );
    }


    public function getInventoryRules(): array
    {
        if (!$this->request->has('variations')) {
            $allowDecimal = $this->allowsDecimalQty();
            $qtyRegex = $allowDecimal ? 'regex:/^\d+(?:[\.,]\d{1,2})?$/' : 'regex:/^\d+$/';

            return [
                'manage_stock' => 'required|boolean',
                'qty' => ['required_if:manage_stock,1','nullable','numeric',$qtyRegex],
                'in_stock' => 'required|boolean',
            ];
        }

        return [];
    }


    public function getProductAttributeRules(): array
    {
        return [
            'attributes.*.attribute_id' => ['required_with:attributes.*.values', Rule::exists('attributes', 'id')],
            'attributes.*.values' => ['required_with:attributes.*.attribute_id', Rule::exists('attribute_values', 'id')],
        ];
    }


    public function getProductVariationsRules(): array
    {
        return [
            'variations.*.name' => 'required_with:variations.*.type',
            'variations.*.type' => ['nullable', 'required_with:variations.*.name', Rule::in(Variation::TYPES)],
            'variations.*.values.*.label' => ['required_with:variations.*.type', new DistinctProductVariationValueLabel()],
            'variations.*.values.*.color' => ['required_if:variations.*.type,color', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'variations.*.values.*.image' => 'required_if:type,image|integer|min:1',
        ];
    }


    public function getProductVariantsRules(): array
    {
        return [
            'variants.*.name' => 'required',
            'variants.*.sku' => 'nullable',
            'variants.*.price' => 'required_if:variants.*.is_active,true|nullable|numeric|min:0|max:99999999999999',
            'variants.*.special_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:99999999999999',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $parts = explode('.', (string) $attribute);
                    $key = $parts[1] ?? null;
                    if ($key === null) {
                        return;
                    }

                    $type = data_get($this->input('variants'), $key . '.special_price_type');
                    if ($type === 'percent') {
                        return;
                    }

                    $price = data_get($this->input('variants'), $key . '.price');

                    if (is_numeric($price) && is_numeric($value) && (float) $value > (float) $price) {
                        $fail('İndirimli fiyat satış fiyatından yüksek olamaz.');
                    }
                },
            ],
            'variants.*.special_price_type' => ['nullable', Rule::in(['fixed', 'percent'])],
            'variants.*.special_price_start' => 'nullable|date|before_or_equal:variants.*.special_price_end',
            'variants.*.special_price_end' => 'nullable|date|after_or_equal:variants.*.special_price_start',
            'variants.*.manage_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.qty' => ['required_if:variants.*.is_active,1','required_if:variants.*.manage_stock,1','nullable','numeric',$this->allowsDecimalQty() ? 'regex:/^\d+(?:[\.,]\d{1,2})?$/' : 'regex:/^\d+$/'],
            'variants.*.in_stock' => 'required_if:variants.*.is_active,1|boolean',
            'variants.*.is_active' => 'required|boolean',
        ];
    }


    public function getProductOptionsRules(): array
    {
        return [
            'options.*.name' => 'required_with:options.*.type',
            'options.*.type' => ['nullable', 'required_with:options.*.name', Rule::in(Option::TYPES)],
            'options.*.is_required' => ['required_with:options.*.name', 'boolean'],
            'options.*.values.*.label' => 'required_if:options.*.type,dropdown,checkbox,checkbox_custom,radio,radio_custom,multiple_select',
            'options.*.values.*.price' => 'nullable|numeric|min:0|max:99999999999999',
            'options.*.values.*.price_type' => ['required', Rule::in(['fixed', 'percent'])],
        ];
    }

    public function getProductMediaRules(): array
    {
        return [
            'product_media' => ['nullable','array'],
            'product_media.*.id' => ['nullable','integer','min:1'],
            'product_media.*.product_id' => ['nullable','integer','min:1'],
            'product_media.*.variant_id' => ['nullable','integer','min:1'],
            'product_media.*.type' => ['required','string', Rule::in(['image','video'])],
            'product_media.*.path' => ['required','string'],
            'product_media.*.poster' => ['nullable','string'],
            'product_media.*.position' => ['nullable','integer','min:0'],
            'product_media.*.is_active' => ['nullable','boolean'],
        ];
    }


    public function messages()
    {
        return array_merge(parent::messages(), [
            'price.required_without' => trans('product::validation.price_field_is_required'),
        ]);
    }


    private function getSlugRules(): array
    {
        $rules = $this->route()->getName() === 'admin.products.update' ? ['required'] : ['sometimes'];
        
        $rules[] = 'regex:/^[a-z0-9-]+$/';
        
        // Global slug uniqueness check
        $rules[] = function ($attribute, $value, $fail) {
            if (\Modules\Support\Entities\UrlSlug::isReserved($value)) {
                $fail('Bu URL rezerve edilmiştir ve kullanılamaz.');
                return;
            }
            
            $productId = $this->id;
            if (!\Modules\Support\Entities\UrlSlug::isAvailable($value, 'product', $productId)) {
                $fail('Bu URL başka bir ürün, kategori veya sayfa tarafından kullanılmaktadır.');
            }
        };

        return $rules;
    }

    private function allowsDecimalQty(): bool
    {
        $unitId = $this->input('sale_unit_id');
        if (!$unitId) {
            return false;
        }
        $unit = Unit::query()->select(['id','is_decimal_stock'])->find($unitId);
        return (bool) optional($unit)->is_decimal_stock;
    }
}
