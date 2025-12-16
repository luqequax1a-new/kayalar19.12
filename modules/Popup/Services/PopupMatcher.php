<?php

namespace Modules\Popup\Services;

use Illuminate\Http\Request;
use Modules\Popup\Entities\Popup;

class PopupMatcher
{
    public function matchForRequest(Request $request): ?Popup
    {
        $device = $this->detectDevice($request);
        $path = '/' . ltrim($request->path(), '/');

        $query = Popup::query()->active()
            ->whereIn('device', ['both', $device]);

        $popups = $query->get();

        $matched = $popups->filter(function (Popup $popup) use ($path) {
            return $this->matchesTarget($popup, $path);
        });

        if ($matched->isEmpty()) {
            return null;
        }

        $selected = $matched->sortByDesc('id')->first();

        return $selected;
    }

    protected function detectDevice(Request $request): string
    {
        $agent = $request->header('User-Agent', '');

        if (preg_match('/Mobile|Android|iP(hone|od|ad)|IEMobile|BlackBerry/i', $agent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    protected function matchesTarget(Popup $popup, string $path): bool
    {
        $scope = $popup->target_scope;
        $data = $popup->targeting ?? [];

        if ($scope === 'all') {
            return true;
        }

        if ($scope === 'homepage') {
            if ($path === '/' || $path === '') {
                return true;
            }

            // Locale prefix'li anasayfalar ("/tr", "/en" gibi) için de anasayfa kabul et.
            // İkinci bir segment yoksa ve ilk segment sadece dil koduysa homepage say.
            $trimmed = trim($path, '/');
            if (!str_contains($trimmed, '/')) {
                // Tek segmentli path ("tr", "en" vb.) anasayfa sayılır.
                return true;
            }

            return false;
        }

        if ($scope === 'cart') {
            return str_contains($path, '/cart');
        }

        if ($scope === 'checkout') {
            return str_contains($path, '/checkout');
        }

        if ($scope === 'custom_url' && !empty($data['patterns'])) {
            foreach ($data['patterns'] as $rule) {
                $value = $rule['value'] ?? '';
                if ($value === '') {
                    continue;
                }

                if (($rule['type'] ?? 'contains') === 'starts_with' && str_starts_with($path, $value)) {
                    return true;
                }

                if (($rule['type'] ?? 'contains') === 'contains' && str_contains($path, $value)) {
                    return true;
                }
            }
        }

        // category & product sayfaları için detaylı eşleştirme controller seviyesinde yapılabilir;
        // şimdilik sadece genel URL desenleriyle sınırlı tutulur.

        return false;
    }
}
