<?php

namespace Modules\Question\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class QuestionTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $this->group('question_information', trans('question::questions.tabs.group.question_information'))
            ->active()
            ->add($this->general());
    }

    private function general()
    {
        return tap(new Tab('question', trans('question::questions.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['customer_name', 'customer_email', 'question', 'answer', 'is_approved']);
            $tab->view('question::admin.tabs.general');
        });
    }
}
