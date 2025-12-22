<section
    x-data="FeaturedCategories({{ $featuredCategories['categories'] }})"
    class="featured-categories-wrap"
>
    <div class="container">
        <div class="featured-categories-header">
            <div class="featured-categories-text">
                <h2 class="title">
                    {{ $featuredCategories['title'] }}
                </h2>

                @if (! empty($featuredCategories['subtitle']))
                    <span class="excerpt">
                        {{ $featuredCategories['subtitle'] }}
                    </span>
                @endif
            </div>

            <div class="featured-categories-tabs-wrapper">
                <div class="featured-categories-tabs-swiper swiper">
                    <ul
                        class="featured-categories-tabs swiper-wrapper"
                        role="tablist"
                    >
                    @foreach ($featuredCategories['categories'] as $key => $tab)
                        <li
                            class="tab-item swiper-slide"
                            :class="classes({{ $key }})"
                            @click="changeTab({{ $key }})"
                            role="tab"
                        >
                                <div class="featured-category-image">
                                    @if (! empty($tab['logo']) && $tab['logo']->path)
                                        @php(
                                            $logoAvif = $tab['logo']->thumb_avif_url
                                                ?? null
                                        )
                                        @php(
                                            $logoWebp = $tab['logo']->thumb_webp_url
                                                ?? null
                                        )
                                        @php(
                                            $logoJpeg = $tab['logo']->thumb_jpeg_url
                                                ?? $tab['logo']->path
                                        )

                                        <picture>
                                            @if ($logoAvif)
                                                <source srcset="{{ $logoAvif }}" type="image/avif">
                                            @endif

                                            @if ($logoWebp)
                                                <source srcset="{{ $logoWebp }}" type="image/webp">
                                            @endif

                                            <img
                                                src="{{ $logoJpeg }}"
                                                alt="{{ $tab['name'] }}"
                                                loading="lazy"
                                            />
                                        </picture>
                                    @else
                                        <div class="image-placeholder icon">
                                            <img
                                                src="{{ asset('build/assets/image-placeholder.png') }}"
                                                alt="{{ $tab['name'] }}"
                                                loading="lazy"
                                            />
                                        </div>
                                    @endif
                                </div>

                                <div class="featured-category-content">
                                    <span class="featured-category-name">
                                        {{ $tab['name'] }}
                                    </span>

                                    @if (! empty($tab['subtitle']))
                                        <span class="featured-category-subtitle">
                                            {{ $tab['subtitle'] }}
                                        </span>
                                    @endif
                                </div>

                            <span class="tab-item-active-border" aria-hidden="true"></span>
                        </li>
                    @endforeach
                    </ul>

                    <div class="featured-categories-tabs-pagination swiper-pagination" aria-hidden="true"></div>
                </div>
            </div>
        </div>

        <div class="featured-categories-body">
            <div class="featured-category-products grid-products products-slider swiper">
                <div class="swiper-wrapper">
                    @foreach (range(0, 7) as $skeleton)
                        <div class="swiper-slide swiper-slide-skeleton">
                            <div class="grid-products-item">
                                @include('storefront::public.partials.product_card_skeleton')
                            </div>
                        </div>
                    @endforeach

                    <template x-for="product in products" :key="product.listing_key || product.id">
                        <div class="swiper-slide">
                            <div class="grid-products-item">
                                @include('storefront::public.partials.product_card')
                            </div>
                        </div>
                    </template>
                </div>

                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
