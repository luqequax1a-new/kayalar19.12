<?php

namespace Modules\Question\Http\Requests;

use Modules\Core\Http\Requests\Request;

class StoreQuestionRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'customer_name' => auth()->check() ? '' : 'required',
            'customer_email' => auth()->check() ? '' : 'required|email',
            'question' => 'required|min:5',
        ];
    }
}
