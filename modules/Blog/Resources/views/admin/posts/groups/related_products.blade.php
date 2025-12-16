@php
    $selectedRelatedProducts = old('related_products', isset($blogPost) ? $blogPost->relatedProducts->pluck('id')->all() : []);
    $selectedRelatedProducts = is_array($selectedRelatedProducts) ? $selectedRelatedProducts : [];
@endphp

<div class="box" id="blog-related-products-box">
    <div class="box-header">
        <h5>{{ trans('blog::admin.blog_posts.form.related_products') }}</h5>
    </div>

    <div class="box-body">
        <div class="form-group">
            <div id="blog-related-products-selector">
                <select
                    id="blog-related-products-select"
                    name="related_products[]"
                    class="form-control"
                    multiple
                    size="6"
                >
                    @foreach ($products ?? [] as $id => $name)
                        <option value="{{ $id }}" @selected(in_array($id, $selectedRelatedProducts))>
                            {{ $name }} (ID: {{ $id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <span class="help-block">
                {{ trans('blog::admin.blog_posts.form.related_products_help') }}
            </span>
        </div>
    </div>
</div>
