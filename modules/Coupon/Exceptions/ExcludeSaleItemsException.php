<?php

namespace Modules\Coupon\Exceptions;

use Exception;
use Illuminate\Http\Response;

class ExcludeSaleItemsException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @return Response
     */
    public function render()
    {
        return response()->json([
            'message' => trans('coupon::messages.exclude_sale_items'),
        ], 403);
    }
}
