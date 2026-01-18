<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('brand::attributes.name'), $errors, $brand, ['required' => true]) }}
        {{ Form::checkbox('is_active', trans('brand::attributes.is_active'), trans('brand::brands.form.enable_the_brand'), $errors, $brand) }}
        
        {{ Form::wysiwyg('description', trans('brand::attributes.description'), $errors, $brand, ['required' => false, 'rows' => 10]) }}
        
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-12">
                <h4 class="tab-content-title">{{ trans('brand::attributes.faq') }}</h4>
                
                <div id="faq-items-wrapper">
                    @foreach($brand->faq_items ?? [] as $index => $faq)
                        <div class="faq-item" style="margin-bottom: 20px; border: 1px solid #e1e2e6; padding: 20px; border-radius: 4px; background: #fff;">
                            <div class="row">
                                <div class="col-md-11">
                                    <div class="form-group">
                                        <label>{{ trans('brand::attributes.question') }}</label>
                                        <input type="text" name="faq_items[{{ $index }}][question]" class="form-control" value="{{ $faq['question'] ?? '' }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('brand::attributes.answer') }}</label>
                                        <textarea name="faq_items[{{ $index }}][answer]" class="form-control" rows="3">{{ $faq['answer'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-1" style="text-align: center; display: flex; align-items: center; justify-content: center;">
                                    <button type="button" class="btn btn-danger btn-sm remove-faq-btn"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button type="button" class="btn btn-default" id="add-faq-btn">
                     <i class="fa fa-plus" aria-hidden="true"></i> {{ trans('brand::attributes.add_faq') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function($) {
        $(document).ready(function() {
            // FAQ Management
            let faqIndex = {{ count($brand->faq_items ?? []) }};
            
            $('#add-faq-btn').on('click', function() {
                let html = `
                    <div class="faq-item" style="margin-bottom: 20px; border: 1px solid #e1e2e6; padding: 20px; border-radius: 4px; background: #fff;">
                        <div class="row">
                            <div class="col-md-11">
                                <div class="form-group">
                                    <label>{{ trans('brand::attributes.question') }}</label>
                                    <input type="text" name="faq_items[${faqIndex}][question]" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>{{ trans('brand::attributes.answer') }}</label>
                                    <textarea name="faq_items[${faqIndex}][answer]" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-1" style="text-align: center; display: flex; align-items: center; justify-content: center;">
                                <button type="button" class="btn btn-danger btn-sm remove-faq-btn"><i class="fa fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                $('#faq-items-wrapper').append(html);
                faqIndex++;
            });
            
            $(document).on('click', '.remove-faq-btn', function() {
                $(this).closest('.faq-item').remove();
            });
        });
    })(jQuery);
</script>
@endpush
