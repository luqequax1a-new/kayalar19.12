<?php

namespace Modules\SizeChart\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\Category\Entities\Category;
use Modules\Tag\Entities\Tag;

class SizeChartTabs extends Tabs
{
    public function make()
    {
        $this->group('size_chart_information', trans('size_chart::size_charts.tabs.group.size_chart_information'))
            ->active()
            ->add($this->general())
            ->add($this->assignments());
    }

    private function general()
    {
        return tap(new Tab('general', trans('size_chart::size_charts.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['title', 'type', 'content_html', 'files.size_chart_image', 'is_active']);
            $tab->view('size_chart::admin.size_charts.tabs.general');
        });
    }

    private function assignments()
    {
        return tap(new Tab('assignments', trans('size_chart::size_charts.tabs.assignments')), function (Tab $tab) {
            $tab->weight(10);
            $tab->view('size_chart::admin.size_charts.tabs.assignments', [
                'categories' => Category::treeList(),
                'tags' => Tag::list(),
            ]);
        });
    }
}
