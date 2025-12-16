<?php

namespace Modules\Geliver\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('admin::sidebar.sales'), function (Item $item) {
                $item->item('Geliver', function (Item $sub) {
                    $sub->weight(20);
                    $sub->route('admin.settings.geliver');
                    $sub->authorize(
                        $this->auth->hasAccess('admin.geliver_settings.edit')
                    );
                });
            });
        });
    }
}

