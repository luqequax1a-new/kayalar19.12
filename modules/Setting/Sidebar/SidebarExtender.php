<?php

namespace Modules\Setting\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->item(trans('setting::sidebar.settings'), function (Item $item) {
                $item->weight(25);
                $item->icon('fa fa-cogs');
                $item->route('admin.settings.edit');
                $item->authorize(
                    $this->auth->hasAccess('admin.settings.edit')
                );
            });
        });

        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('admin::sidebar.automations'), function (Item $item) {
                $item->weight(21);
                $item->icon('fa fa-tasks');
                $item->authorize(true);

                // Email Campaigns Folder
                $item->item(trans('admin::sidebar.email_automations'), function (Item $item) {
                    $item->weight(1);
                    $item->icon('fa fa-envelope');
                    $item->authorize($this->auth->hasAccess('admin.settings.edit'));

                    $item->item(trans('admin::sidebar.abandoned_carts'), function (Item $item) {
                        $item->weight(1);
                        $item->icon('fa fa-shopping-cart');
                        $item->route('admin.abandoned_carts.index');
                        $item->authorize(
                            $this->auth->hasAccess('admin.coupons.index')
                        );
                    });

                    $item->item(trans('admin::sidebar.settings'), function (Item $item) {
                        $item->weight(2);
                        $item->icon('fa fa-gear');
                        $item->route('admin.settings.abandoned_cart');
                        $item->authorize(
                            $this->auth->hasAccess('admin.settings.edit')
                        );
                    });
                });

                // Review Campaigns Folder
                $item->item(trans('admin::sidebar.review_campaigns'), function (Item $item) {
                    $item->weight(2);
                    $item->icon('fa fa-star');
                    $item->authorize($this->auth->hasAccess('admin.settings.edit'));

                    $item->item('Yorum Kuponları', function (Item $item) {
                        $item->weight(1);
                        $item->icon('fa fa-gift');
                        $item->route('admin.review_coupons.index');
                        $item->authorize(
                            $this->auth->hasAccess('admin.coupons.index')
                        );
                    });

                    $item->item(trans('admin::sidebar.settings'), function (Item $item) {
                        $item->weight(2);
                        $item->icon('fa fa-gear');
                        $item->route('admin.settings.review_campaign');
                        $item->authorize(
                            $this->auth->hasAccess('admin.settings.edit')
                        );
                    });
                });

                // Other items
                $item->item(trans('admin::sidebar.whatsapp_module'), function (Item $item) {
                    $item->weight(3);
                    $item->icon('fa fa-whatsapp');
                    $item->route('admin.settings.whatsapp_module');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item('Etiket-Görsel', function (Item $item) {
                    $item->weight(4);
                    $item->route('admin.tag_badges.index');
                    $item->icon('fa fa-image');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item('Özelleştirmeler', function (Item $item) {
                    $item->weight(6);
                    $item->icon('fa fa-magic');
                    $item->route('admin.settings.customizations');
                    $item->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });
            });
        });
    }
}
