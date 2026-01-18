<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Modules\Media\Entities\File;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Artisan;
use Modules\Admin\Ui\Facades\TabManager;
use Modules\Support\Services\PWAService;
use Illuminate\Contracts\Foundation\Application;
use Modules\Setting\Http\Requests\UpdateSettingRequest;

class SettingController
{
    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function edit()
    {
        $settings = setting()->all();
        $tabs = TabManager::get('settings');

        return view('setting::admin.settings.edit', compact('settings', 'tabs'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSettingRequest $request
     * @param PWAService $PWAService
     *
     * @return Application|\Illuminate\Foundation\Application|RedirectResponse|Redirector
     */
    public function update(UpdateSettingRequest $request, PWAService $PWAService)
    {
        $this->handleMaintenanceMode($request);

        if (setting('pwa_icon') !== request('pwa_icon')) {
            $file = File::find(request('pwa_icon'));
            $file && $PWAService->generateIcons($file);
            $PWAService->updatePWAVersionInServiceWorkerJs();
        }

        setting($request->except('_token', '_method'));
        if ($request->get('context') === 'review_campaign') {
            return redirect()->route('admin.settings.review_campaign')
                ->with('success', trans('setting::messages.settings_updated'));
        }

        if ($request->get('context') === 'whatsapp_module') {
            return redirect()->route('admin.settings.whatsapp_module')
                ->with('success', trans('setting::messages.settings_updated'));
        }

        if ($request->get('context') === 'abandoned_cart') {
            return redirect()->route('admin.settings.abandoned_cart')
                ->with('success', trans('setting::messages.settings_updated'));
        }

        if ($request->get('context') === 'customizations') {
            return redirect()->route('admin.settings.customizations')
                ->with('success', trans('setting::messages.settings_updated'));
        }

        if ($request->get('context') === 'search') {
            return redirect()->route('admin.settings.edit', ['tab' => 'search'])
                ->with('success', trans('setting::messages.settings_updated'));
        }

        return redirect(non_localized_url())
            ->with('success', trans('setting::messages.settings_updated'));
    }


    private function handleMaintenanceMode($request)
    {
        if ($request->maintenance_mode) {
            Artisan::call('down');
        } else if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }
    }
}
