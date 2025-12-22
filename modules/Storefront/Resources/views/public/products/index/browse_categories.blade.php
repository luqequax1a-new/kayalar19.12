<ul class="list-inline browse-categories">
    @foreach ($categories as $category)
        @php
            $categoryBannerPayload = [
                'path' => optional($category->banner)->path,
            ];
        @endphp
        <li :class="{ active: queryParams.category === '{{ $category->slug }}' }">
            @if ($category->items->isNotEmpty())
                <i
                    class="las la-angle-right"
                    @click="
                        $($el).toggleClass('open');
                        $($el).siblings('ul').slideToggle(200);
                    "
                >
                </i>
            @endif
            
            <a
                href="{{ route('products.index', ['category' => $category->slug]) }}"
                title="{{ $category->name }}"
                @click.prevent='changeCategory({
                    name: "{{ addslashes($category->name) }}",
                    banner: @json($categoryBannerPayload),
                    slug: "{{ $category->slug }}",
                    meta_title: "{{ addslashes($category->meta_title ?: ($category->name . ' | ' . setting('store_name'))) }}"
                })'
            >
                {{ $category->name }}
            </a>

            @if ($category->items->isNotEmpty())
                @include('storefront::public.products.index.browse_sub_categories', ['subCategories' => $category->items])
            @endif
        </li>
    @endforeach
</ul>
