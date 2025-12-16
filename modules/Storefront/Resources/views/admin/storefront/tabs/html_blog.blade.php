<div class="accordion-box-content">
    <div class="row">
        <div class="col-md-8">
            {{ Form::checkbox('storefront_html_blog_enabled', trans('storefront::attributes.section_status'), 'Html Blog bölümünü etkinleştir', $errors, $settings) }}
        </div>
    </div>

    <div class="tab-content clearfix">
        <div class="panel-wrap">
            <div class="row">
                <div class="col-md-8">
                    {{ Form::wysiwyg('storefront_html_blog_content', 'Html İçerik', $errors, $settings, ['rows' => 8]) }}
                </div>
            </div>
        </div>
    </div>
</div>
