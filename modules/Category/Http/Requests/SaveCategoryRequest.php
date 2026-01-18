<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Category\Entities\Category;
use Modules\Core\Http\Requests\Request;

class SaveCategoryRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'category::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'slug' => $this->getSlugRules(),
            'is_active' => 'required|boolean',
            'faq_items' => 'nullable|array',
            'faq_items.*.question' => 'required|string',
            'faq_items.*.answer' => 'required|string',
        ];
    }


    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.categories.update'
            ? ['required']
            : ['nullable'];
        
        $rules[] = 'regex:/^[a-z0-9-]+$/';
        
        // Global slug uniqueness check
        $rules[] = function ($attribute, $value, $fail) {
            if (\Modules\Support\Entities\UrlSlug::isReserved($value)) {
                $fail('Bu URL rezerve edilmiştir ve kullanılamaz.');
                return;
            }
            
            $categoryId = $this->id;
            if (!\Modules\Support\Entities\UrlSlug::isAvailable($value, 'category', $categoryId)) {
                $fail('Bu URL başka bir ürün, kategori veya sayfa tarafından kullanılmaktadır.');
            }
        };

        return $rules;
    }
}
