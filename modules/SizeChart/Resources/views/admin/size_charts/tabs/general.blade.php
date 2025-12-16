<div class="row">
    <div class="col-md-8">
        {{ Form::text('title', trans('size_chart::attributes.title'), $errors, $sizeChart, ['required' => true]) }}

        {{ Form::select('type', trans('size_chart::attributes.type'), $errors, trans('size_chart::size_charts.types'), $sizeChart) }}

        <div class="clearfix"></div>

        <div data-size-chart-html-fields>
            {{ Form::wysiwyg('content_html', trans('size_chart::attributes.content_html'), $errors, $sizeChart) }}
        </div>

        {{ Form::checkbox('is_active', trans('size_chart::attributes.is_active'), trans('size_chart::size_charts.form.enable_size_chart'), $errors, $sizeChart) }}
    </div>
</div>

@if (auth()->user()->hasAccess('admin.media.index'))
    <div class="media-picker-divider"></div>

    <div data-size-chart-image-fields>
        @include('media::admin.image_picker.single', [
            'title' => trans('size_chart::attributes.image'),
            'inputName' => 'files[size_chart_image]',
            'file' => $sizeChart->image,
        ])
    </div>
@endif
