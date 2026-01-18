<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('unit::attributes.name'), $errors, $unit, ['required' => true]) }}
        {{ Form::text('label', trans('unit::attributes.label'), $errors, $unit, ['required' => true]) }}
        {{ Form::text('short_suffix', trans('unit::attributes.short_suffix'), $errors, $unit, ['placeholder' => 'm, kg, lt...']) }}
        {{ Form::number('min', trans('unit::attributes.min'), $errors, $unit, ['required' => true, 'step' => '0.01', 'min' => 0]) }}
        {{ Form::number('step', trans('unit::attributes.step'), $errors, $unit, ['required' => true, 'step' => '0.01', 'min' => 0]) }}
        {{ Form::number('default_qty', trans('unit::attributes.default_qty'), $errors, $unit, ['step' => '0.01', 'min' => 0]) }}
        {{ Form::textarea('info_top', trans('unit::attributes.info_top'), $errors, $unit, ['rows' => 3]) }}
        {{ Form::textarea('info_bottom', trans('unit::attributes.info_bottom'), $errors, $unit, ['rows' => 3]) }}
        {{ Form::checkbox('is_decimal_stock', trans('unit::attributes.is_decimal_stock'), trans('unit::units.form.enable_decimal_stock'), $errors, $unit) }}
    </div>
</div>
