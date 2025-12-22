<?php

namespace Modules\Account\Http\Controllers;

use Modules\Support\Country;
use Illuminate\Routing\Controller;
use Modules\Address\Entities\Address;
use Modules\Address\Entities\DefaultAddress;
use Modules\Account\Http\Requests\SaveAddressRequest;

class AccountAddressController extends Controller
{
    public function index()
    {
        return view('storefront::public.account.addresses.index', [
            'addresses' => auth()->user()->addresses->keyBy('id'),
            'defaultAddress' => auth()->user()->defaultAddress,
            'countries' => Country::supported(),
        ]);
    }


    public function store(SaveAddressRequest $request)
    {
        $payload = array_merge($request->all(), [
            'customer_id' => auth()->id(),
            'user_id' => auth()->id(),
        ]);

        $address = auth()->user()->addresses()->create($payload);

        return response()->json([
            'address' => $address,
            'message' => trans('account::messages.address_created'),
        ]);
    }


    public function update(SaveAddressRequest $request, $id)
    {
        $address = auth()->user()->addresses()->whereKey($id)->firstOrFail();
        $address->update($request->all());

        return response()->json([
            'address' => $address,
            'message' => trans('account::messages.address_updated'),
        ]);
    }


    public function destroy($id)
    {
        auth()->user()->addresses()->whereKey($id)->firstOrFail()->delete();

        return response()->json([
            'message' => trans('account::messages.address_deleted'),
        ]);
    }


    public function changeDefault()
    {
        $type = request('type');
        $addressId = request('address_id');

        $payload = [];

        if ($type === Address::TYPE_BILLING) {
            $payload['default_billing_address_id'] = $addressId;
        } else {
            $payload['default_shipping_address_id'] = $addressId;
        }

        DefaultAddress::updateOrCreate(
            ['customer_id' => auth()->id()],
            $payload
        );

        return trans('account::messages.default_address_updated');
    }
}
