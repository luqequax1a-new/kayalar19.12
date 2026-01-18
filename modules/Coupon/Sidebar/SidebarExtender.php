<?php

namespace Modules\Coupon\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item('İndirimler', function (Item $item) {
                $item->icon('fa fa-percent');
                $item->weight(20);
                $item->authorize(true);

                $item->item(trans('coupon::coupons.coupons'), function (Item $item) {
                    $item->icon('fa fa-tags');
                    $item->weight(1);
                    $item->route('admin.coupons.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.coupons.index')
                    );
                });

                $item->item('Sepet Teklifleri', function (Item $item) {
                    $item->icon('fa fa-bullhorn');
                    $item->weight(2);
                    $item->route('admin.cart_upsell_rules.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.coupons.index')
                    );
                });
            });
        });
    }
}
