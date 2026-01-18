<?php

namespace Modules\Page\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Page\Entities\Page;
use Modules\Core\Http\Requests\Request;

class SavePageRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'page::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'slug' => $this->getSlugRules(),
            'name' => 'required',
            'body' => 'required',
            'is_active' => 'required|boolean',
        ];
    }


    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.pages.update'
            ? ['required']
            : ['sometimes'];
        
        $rules[] = 'regex:/^[a-z0-9-]+$/';
        
        // Global slug uniqueness check
        $rules[] = function ($attribute, $value, $fail) {
            if (\Modules\Support\Entities\UrlSlug::isReserved($value)) {
                $fail('Bu URL rezerve edilmiştir ve kullanılamaz.');
                return;
            }
            
            $pageId = $this->id;
            if (!\Modules\Support\Entities\UrlSlug::isAvailable($value, 'page', $pageId)) {
                $fail('Bu URL başka bir ürün, kategori veya sayfa tarafından kullanılmaktadır.');
            }
        };

        return $rules;
    }
}
