<?php

namespace Modules\SizeChart\Sidebar;

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
                $item->item(trans('size_chart::size_charts.size_charts'), function (Item $item) {
                    $item->icon('fa fa-arrows-v');
                    $item->weight(36);
                    $item->route('admin.size_charts.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.size_charts.index')
                    );
                });
            });
        });
    }
}
