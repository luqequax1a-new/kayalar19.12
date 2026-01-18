<?php

namespace Modules\Unit\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('product::sidebar.products'), function (Item $item) {
                $item->item('Units', function (Item $item) {
                    $item->weight(11);
                    $item->route('admin.units.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.units.index')
                    );
                });
            });
        });
    }
}

