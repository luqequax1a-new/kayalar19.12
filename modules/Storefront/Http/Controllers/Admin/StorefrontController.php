<?php

namespace Modules\Storefront\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Storefront\Http\Requests\SaveStorefrontRequest;

class StorefrontController
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $settings = setting()->all();
        $tabs = TabManager::get('storefront');

        return view('storefront::admin.storefront.edit', compact('settings', 'tabs'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(SaveStorefrontRequest $request)
    {
        setting($request->except('_token', '_method'));

        return back()->withSuccess(trans('admin::messages.resource_updated', ['resource' => trans('setting::settings.settings')]));
    }


    public function updateHomePageSectionsOrder(Request $request)
    {
        $order = $request->input('order', []);

        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        $order = is_array($order) ? array_values($order) : [];

        setting(['storefront_home_page_sections_order' => $order]);

        return response()->json(['success' => true]);
    }


    public function updateProductPageSectionsOrder(Request $request)
    {
        $order = $request->input('order', []);

        if (is_string($order)) {
            $order = json_decode($order, true);
        }

        $order = is_array($order) ? array_values($order) : [];

        setting(['storefront_product_page_sections_order' => $order]);

        return response()->json(['success' => true]);
    }
}
