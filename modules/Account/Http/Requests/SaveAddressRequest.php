<?php

namespace Modules\Account\Http\Requests;

use Modules\Core\Http\Requests\Request;

class SaveAddressRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'account::attributes.addresses';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => ['required', 'in:shipping,billing'],
            'address_title' => ['nullable', 'string', 'max:191'],
            'first_name' => ['required_if:type,shipping', 'nullable', 'string', 'max:191'],
            'last_name' => ['required_if:type,shipping', 'nullable', 'string', 'max:191'],
            'address_1' => ['required'],
            'city_id' => ['required_if:country,TR', 'nullable'],
            'district_id' => ['required_if:country,TR', 'nullable'],
            'city' => ['required_unless:country,TR'],
            'zip' => ['nullable'],
            'country' => ['required'],
            'state' => ['required_unless:country,TR'],
            'phone' => ['required_if:type,shipping', 'nullable'],
            'invoice_title' => ['nullable', 'string', 'max:191'],
            'invoice_tax_number' => ['nullable', 'string', 'max:50'],
            'invoice_tax_office' => ['nullable', 'string', 'max:191'],
            'billing_email' => ['nullable', 'email', 'max:191'],
        ];
    }
}
