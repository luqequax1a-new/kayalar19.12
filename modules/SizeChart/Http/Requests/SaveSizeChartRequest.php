<?php

namespace Modules\SizeChart\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Core\Http\Requests\Request;

class SaveSizeChartRequest extends Request
{
    protected $availableAttributes = 'size_chart::attributes';

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['image', 'html'])],
            'is_active' => ['nullable', 'boolean'],
            'content_html' => ['nullable', 'string'],
            'files.size_chart_image' => ['nullable'],
            'categories' => ['array'],
            'categories.*' => ['integer'],
            'tags' => ['array'],
            'tags.*' => ['integer'],
            'product_id' => ['nullable', 'integer'],
        ];
    }
}
