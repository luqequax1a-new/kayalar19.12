<?php

namespace Modules\Support\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\Support\Entities\UrlSlug;

class SlugController extends Controller
{
    public function check()
    {
        $slug = request('slug');
        $type = request('type');
        $entityId = request('entityId');

        if (is_null($slug) || $slug === '') {
            return response()->json(['available' => true]);
        }

        if (UrlSlug::isReserved($slug)) {
            return response()->json([
                'available' => false,
                'message' => 'Bu URL rezerve edilmiştir ve kullanılamaz.'
            ]);
        }

        if (!UrlSlug::isAvailable($slug, $type, $entityId)) {
            return response()->json([
                'available' => false,
                'message' => 'Bu URL başka bir ürün, kategori veya sayfa tarafından kullanılmaktadır.'
            ]);
        }

        return response()->json(['available' => true]);
    }
}
