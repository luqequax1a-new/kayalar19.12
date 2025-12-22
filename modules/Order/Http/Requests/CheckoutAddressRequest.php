<?php

namespace Modules\Order\Http\Requests;

use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Http\Requests\Request;
use Modules\Cart\Facades\Cart;
use Modules\Payment\Facades\Gateway;
use Modules\Checkout\Exceptions\CheckoutException;
use Modules\Address\Entities\Address as UserAddress;

class CheckoutAddressRequest extends Request
{
    protected $availableAttributes = 'checkout::attributes';

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        if (!Cart::allItemsAreVirtual() && !$this->input('shipping_method')) {
            throw new CheckoutException(trans('checkout::messages.no_shipping_method'));
        }

        $hasDifferent = $this->boolean('has_different_billing');
        if (!$this->has('has_different_billing')) {
            $hasDifferent = (bool) $this->input('ship_to_a_different_address');
            $this->merge(['has_different_billing' => $hasDifferent ? 1 : 0]);
        }
        $this->merge(['ship_to_a_different_address' => $hasDifferent]);

        // Map legacy fields to new schema
        $shipping = $this->input('shipping', []);
        $billing = $this->input('billing', []);
        $invoice = $this->input('invoice', []);

        if (!isset($shipping['address_line']) && isset($shipping['address_1'])) {
            $shipping['address_line'] = $shipping['address_1'];
        }
        if (!isset($billing['address_line']) && isset($billing['address_1'])) {
            $billing['address_line'] = $billing['address_1'];
        }

        if ($hasDifferent) {
            $billing['company_name'] = $billing['company_name'] ?? ($invoice['title'] ?? null);
            $billing['tax_number'] = $billing['tax_number'] ?? ($invoice['tax_number'] ?? null);
            $billing['tax_office'] = $billing['tax_office'] ?? ($invoice['tax_office'] ?? null);
            $billing['phone'] = $billing['phone'] ?? ($this->input('customer_phone') ?? null);
        }

        // Hydrate from saved address IDs when provided
        $shippingAddressId = $this->input('shipping_address_id') ?? $this->input('shippingAddressId');
        $billingAddressId = $this->input('billing_address_id') ?? $this->input('billingAddressId');

        if (auth()->guest()) {
            $shippingAddressId = null;
            $billingAddressId = null;
        }

        if ($shippingAddressId) {
            try {
                $addr = UserAddress::query()
                    ->where('id', (int) $shippingAddressId)
                    ->where(function ($q) {
                        if (auth()->check()) {
                            $q->where('user_id', auth()->id());
                        }
                    })
                    ->first();

                if ($addr) {
                    $shipping = array_filter([
                        'first_name' => $shipping['first_name'] ?? $addr->first_name,
                        'last_name' => $shipping['last_name'] ?? $addr->last_name,
                        'phone' => $shipping['phone'] ?? $addr->phone,
                        'address_line' => $shipping['address_line'] ?? ($addr->address_line ?? $addr->address_1),
                        'city_id' => $shipping['city_id'] ?? $addr->city_id,
                        'district_id' => $shipping['district_id'] ?? $addr->district_id,
                        'city' => $shipping['city'] ?? $addr->city,
                        'state' => $shipping['state'] ?? $addr->state,
                    ], function ($v) {
                        return !is_null($v) && $v !== '';
                    });
                }
            } catch (\Throwable $e) {
            }
        }

        if ($hasDifferent && $billingAddressId) {
            try {
                $addr = UserAddress::query()
                    ->where('id', (int) $billingAddressId)
                    ->where(function ($q) {
                        if (auth()->check()) {
                            $q->where('user_id', auth()->id());
                        }
                    })
                    ->first();

                if ($addr) {
                    $billing = array_filter([
                        'company_name' => $billing['company_name'] ?? $addr->company_name,
                        'tax_number' => $billing['tax_number'] ?? $addr->tax_number,
                        'tax_office' => $billing['tax_office'] ?? $addr->tax_office,
                        'phone' => $billing['phone'] ?? $addr->phone,
                        'billing_email' => $billing['billing_email'] ?? $addr->billing_email,
                        'address_line' => $billing['address_line'] ?? ($addr->address_line ?? $addr->address_1),
                        'city_id' => $billing['city_id'] ?? $addr->city_id,
                        'district_id' => $billing['district_id'] ?? $addr->district_id,
                        'city' => $billing['city'] ?? $addr->city,
                        'state' => $billing['state'] ?? $addr->state,
                    ], function ($v) {
                        return !is_null($v) && $v !== '';
                    });
                }
            } catch (\Throwable $e) {
            }
        }

        $this->merge([
            'shipping' => $shipping,
            'billing' => $billing,
            'shipping_address_id' => $shippingAddressId,
            'billing_address_id' => $billingAddressId,
        ]);
    }

    public function rules(): array
    {
        $hasDifferentBilling = $this->boolean('has_different_billing') || $this->boolean('ship_to_a_different_address');
        $usingSavedShipping = $this->filled('shipping_address_id');
        $usingSavedBilling = $hasDifferentBilling && $this->filled('billing_address_id');

        $rules = [
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required'],
            'create_an_account' => 'boolean',
            'password' => 'required_if:create_an_account,1',
            'has_different_billing' => ['nullable', 'boolean'],
            'ship_to_a_different_address' => ['nullable', 'boolean'],
            'payment_method' => ['required', Rule::in(Gateway::names())],
            'terms_and_conditions' => 'accepted',
            'shipping_method' => Cart::allItemsAreVirtual() ? 'nullable' : 'required',
            'shipping_address_id' => ['nullable', 'integer'],
            'billing_address_id' => ['nullable', 'integer'],
        ];

        if (!$usingSavedShipping) {
            $rules = array_merge($rules, [
                'shipping.first_name' => ['required', 'string', 'max:100'],
                'shipping.last_name' => ['required', 'string', 'max:100'],
                'shipping.phone' => ['required', 'string', 'max:50'],
                'shipping.city_id' => ['required_without:shipping.city', 'nullable', 'integer'],
                'shipping.city' => ['required_without:shipping.city_id', 'nullable', 'string', 'max:255'],
                'shipping.district_id' => ['required_without:shipping.state', 'nullable', 'integer'],
                'shipping.state' => ['required_without:shipping.district_id', 'nullable', 'string', 'max:255'],
                'shipping.address_line' => ['required', 'string', 'max:500'],
            ]);
        } else {
            $rules['shipping_address_id'] = ['required', 'integer'];
        }

        if ($hasDifferentBilling) {
            if ($usingSavedBilling) {
                $rules['billing_address_id'] = ['required', 'integer'];
            } else {
                $rules = array_merge($rules, [
                    'billing.company_name' => ['required', 'string', 'max:255'],
                    'billing.tax_number' => ['required', 'string', 'max:50'],
                    'billing.tax_office' => ['required', 'string', 'max:255'],
                    'billing.phone' => ['nullable', 'string', 'max:50'],
                    'billing.billing_email' => ['nullable', 'email', 'max:191'],
                    'billing.city_id' => ['required_without:billing.city', 'nullable', 'integer'],
                    'billing.city' => ['required_without:billing.city_id', 'nullable', 'string', 'max:255'],
                    'billing.district_id' => ['required_without:billing.state', 'nullable', 'integer'],
                    'billing.state' => ['required_without:billing.district_id', 'nullable', 'string', 'max:255'],
                    'billing.address_line' => ['required', 'string', 'max:500'],
                ]);
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'shipping.first_name' => 'Kargo ad',
            'shipping.last_name' => 'Kargo soyad',
            'shipping.phone' => 'Kargo telefon',
            'shipping.city_id' => 'Kargo il',
            'shipping.city' => 'Kargo il',
            'shipping.district_id' => 'Kargo ilçe',
            'shipping.state' => 'Kargo ilçe',
            'shipping.address_line' => 'Kargo adresi',

            'billing.company_name' => 'Fatura firma adı',
            'billing.tax_number' => 'Vergi numarası',
            'billing.tax_office' => 'Vergi dairesi',
            'billing.phone' => 'Fatura telefon',
            'billing.billing_email' => 'Fatura e-posta',
            'billing.city_id' => 'Fatura il',
            'billing.city' => 'Fatura il',
            'billing.district_id' => 'Fatura ilçe',
            'billing.state' => 'Fatura ilçe',
            'billing.address_line' => 'Fatura adresi',
        ];
    }
}
