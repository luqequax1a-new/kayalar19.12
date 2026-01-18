<?php

namespace Modules\Coupon\Http\Controllers\Admin;

use Illuminate\Support\Facades\Mail;
use Modules\Coupon\Entities\Coupon;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Coupon\Http\Requests\SaveCouponRequest;
use Modules\Coupon\Mail\CouponAssigned;

class CouponController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Coupon::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'coupon::coupons.coupon';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'coupon::admin.coupons';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveCouponRequest::class;

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->except(array_keys(request()->query()))
        );

        $this->searchable($entity);

        // Send email notification if coupon is assigned to a specific customer
        if ($entity->customer_id && $entity->customer) {
            try {
                Mail::to($entity->customer->email)->send(new CouponAssigned($entity, $entity->customer));
            } catch (\Exception $e) {
                \Log::error('Failed to send coupon assignment email: ' . $e->getMessage());
            }
        }

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo($entity);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('admin::messages.resource_created', ['resource' => $this->getLabel()]),
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_created', ['resource' => $this->getLabel()]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $entity = $this->getEntity($id);
        $oldCustomerId = $entity->customer_id;

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->except(array_keys(request()->query()))
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        $this->searchable($entity);

        // Send email notification if customer was assigned or changed
        if ($oldCustomerId != $entity->customer_id && $entity->customer_id && $entity->customer) {
            try {
                Mail::to($entity->customer->email)->send(new CouponAssigned($entity, $entity->customer));
            } catch (\Exception $e) {
                \Log::error('Failed to send coupon assignment email: ' . $e->getMessage());
            }
        }

        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo($entity)
                ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]),
                ],
                200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess(trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]));
    }
}
