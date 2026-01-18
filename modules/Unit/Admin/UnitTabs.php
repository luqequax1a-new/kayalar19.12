<?php

namespace Modules\Unit\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class UnitTabs extends Tabs
{
    public function make()
    {
        $this->group('unit_information', trans('unit::units.tabs.group.unit_information'))
            ->active()
            ->add($this->general());
    }

    private function general()
    {
        return tap(new Tab('general', trans('unit::units.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'label', 'short_suffix', 'min', 'step', 'default_qty', 'is_decimal_stock', 'info_top', 'info_bottom']);
            $tab->view('unit::admin.units.tabs.general');
        });
    }
}
