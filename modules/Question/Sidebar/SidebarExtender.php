<?php

namespace Modules\Question\Sidebar;

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
                $item->item(trans('question::questions.questions'), function (Item $item) {
                    $item->weight(45);
                    $item->route('admin.questions.index');
                    $item->authorize(
                        true
                    );
                });
            });
        });
    }
}
