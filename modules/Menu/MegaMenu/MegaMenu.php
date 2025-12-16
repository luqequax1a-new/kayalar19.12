<?php

namespace Modules\Menu\MegaMenu;

use Illuminate\Support\Facades\Cache;
use Modules\Menu\Entities\Menu as MenuModel;

class MegaMenu
{
    private $menuId;


    public function __construct($menuId)
    {
        $this->menuId = $menuId;
    }


    public function menus()
    {
        $key = 'storefront:globals:' . locale() . ':mega_menu:' . $this->menuId . ':v1';

        return Cache::store('file')->remember($key, now()->addMinutes(10), function () {
            return $this->getMenus()->map(function ($menu) {
                return new Menu($menu);
            });
        });
    }


    private function getMenus()
    {
        return MenuModel::for($this->menuId)->where('menu_id', $this->menuId);
    }
}
