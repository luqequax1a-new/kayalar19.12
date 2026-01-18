<?php

namespace Modules\Unit\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Modules\Unit\Entities\Unit;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Unit\Http\Requests\SaveUnitRequest;

class UnitController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Unit::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'unit::units.unit';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'unit::admin.units';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveUnitRequest::class;

    public function table(Request $request)
    {
        return $this->getModel()->table($request);
    }

    public function createFormData()
    {
        $tabs = new \Modules\Unit\Admin\UnitTabs();
        $tabs->make();

        return [
            'tabs' => $tabs,
        ];
    }

    public function editFormData($id)
    {
        $tabs = new \Modules\Unit\Admin\UnitTabs();
        $tabs->make();

        return [
            'tabs' => $tabs,
        ];
    }
}
