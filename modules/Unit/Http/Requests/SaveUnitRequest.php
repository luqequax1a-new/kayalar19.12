<?php

namespace Modules\Unit\Http\Requests;

use Modules\Core\Http\Requests\Request;

class SaveUnitRequest extends Request
{
    /**
     * Available attributes for this request.
     *
     * @var string
     */
    protected $availableAttributes = 'unit::attributes';

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_decimal_stock' => $this->has('is_decimal_stock') ? true : false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'label' => 'required|max:255',
            'info_top' => 'nullable|max:1000',
            'info_bottom' => 'nullable|max:1000',
            'step' => 'required|numeric|min:0',
            'min' => 'required|numeric|min:0',
            'default_qty' => 'nullable|numeric|min:0',
            'is_decimal_stock' => 'nullable|boolean',
            'short_suffix' => 'nullable|max:255',
        ];
    }
}
