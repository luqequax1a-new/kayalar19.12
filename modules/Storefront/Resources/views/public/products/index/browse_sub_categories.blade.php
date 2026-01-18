<ul class="{{ !($isRoot ?? false) ? 'sub-menu' : '' }}" x-show="{{ ($isRoot ?? false) ? 'true' : 'open' }}" x-collapse>
    @foreach ($subCategories as $subCategory)
        @php
            $categoryBannerPayload = [
                'path' => optional($subCategory->banner)->path,
            ];
        @endphp
        <li 
            class="tree-item" 
            x-data="{ open: false }" 
            x-init="$nextTick(() => { 
                if ($el.querySelector('.tree-item-link.active') || queryParams.category === '{{ $subCategory->slug }}') {
                    open = true;
                }
            })"
        >
            <div 
                class="tree-item-link-wrap d-flex align-items-center"
                :class="{ active: queryParams.category === '{{ $subCategory->slug }}' }"
                @if ($subCategory->items->isNotEmpty())
                    @click="open = !open"
                @endif
            >
                <a
                    href="{{ route('products.index', ['category' => $subCategory->slug]) }}"
                    class="tree-item-link"
                    :class="{ active: queryParams.category === '{{ $subCategory->slug }}' }"
                    title="{{ $subCategory->name }}"
                    @click.stop.prevent='changeCategory({
                        name: "{{ addslashes($subCategory->name) }}",
                        banner: @json($categoryBannerPayload),
                        slug: "{{ $subCategory->slug }}",
                        meta_title: "{{ addslashes($subCategory->meta_title ?: ($subCategory->name . ' | ' . setting('store_name'))) }}"
                    })'
                >
                    <span class="link-text">{{ $subCategory->name }}</span>
                </a>
                
                @if ($subCategory->items->isNotEmpty())
                    <div 
                        class="tree-toggle" 
                        :class="{ 'is-open': open }"
                        @click.stop="open = !open"
                    >
                        <i class="las la-angle-right"></i>
                    </div>
                @endif
            </div>

            @if ($subCategory->items->isNotEmpty())
                @include('storefront::public.products.index.browse_sub_categories', ['subCategories' => $subCategory->items, 'isRoot' => false])
            @endif
        </li>
    @endforeach
</ul>
