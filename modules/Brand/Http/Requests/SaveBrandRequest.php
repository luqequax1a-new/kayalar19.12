<?php

namespace Modules\Brand\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Brand\Entities\Brand;
use Modules\Core\Http\Requests\Request;

class SaveBrandRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'brand::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'slug' => $this->getSlugRules(),
            'description' => ['nullable', 'string'],
            'faq_items' => ['nullable', 'array'],
            'faq_items.*.question' => ['required', 'string'],
            'faq_items.*.answer' => ['required', 'string'],
        ];
    }


    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.brands.update'
            ? ['required']
            : ['sometimes'];

        $slug = Brand::withoutGlobalScope('active')->where('id', $this->id)->value('slug');

        $rules[] = Rule::unique('brands', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
