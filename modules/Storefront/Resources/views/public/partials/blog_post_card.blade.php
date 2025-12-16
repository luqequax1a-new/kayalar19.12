<div class="blog-post-card">
    <div class="blog-post">
        <a
            href="{{ route('blog_posts.show', $blogPost->slug) }}"
            class="blog-post-featured-image overflow-hidden"
        >
            @if ($blogPost->featured_image->path)
                @php(
                    $imageAvif = $blogPost->featured_image->grid_avif_url
                        ?? $blogPost->featured_image->detail_avif_url
                        ?? null
                )
                @php(
                    $imageWebp = $blogPost->featured_image->grid_webp_url
                        ?? $blogPost->featured_image->detail_webp_url
                        ?? null
                )
                @php(
                    $imageJpeg = $blogPost->featured_image->grid_jpeg_url
                        ?? $blogPost->featured_image->detail_jpeg_url
                        ?? $blogPost->featured_image->path
                )

                <picture>
                    @if ($imageAvif)
                        <source srcset="{{ $imageAvif }}" type="image/avif">
                    @endif

                    @if ($imageWebp)
                        <source srcset="{{ $imageWebp }}" type="image/webp">
                    @endif

                    <img src="{{ $imageJpeg }}" alt="Featured image" loading="lazy" />
                </picture>
            @else
                <div class="image-placeholder">
                    <img src="{{ asset('build/assets/image-placeholder.png') }}" alt="Featured image" loading="lazy" />
                </div>
            @endif
        </a>

        <div class="blog-post-body">
            <ul class="list-inline blog-post-meta">
                <li class="d-flex align-items-center">
                    <i class="las la-user"></i>

                    @if ($blogPost->user_name && $blogPost->user_name !== 'Admin User')
                        {{ $blogPost->user_name }}
                    @else
                        Kayalar Manifatura
                    @endif
                </li>

                <li class="d-flex align-items-center">
                    <i class="las la-calendar"></i>

                    {{ $blogPost->created_at->locale('tr_TR')->translatedFormat('d M, Y') }}
                </li>
            </ul>

            <h4 class="blog-post-title">
                <a href="{{ route('blog_posts.show', $blogPost->slug) }}">
                    {{ $blogPost->title }}
                </a>
            </h4>

            <a
                href="{{ route('blog_posts.show', $blogPost->slug) }}"
                class="blog-post-read-more"
            >
                {{ trans("storefront::blog.blog_posts.read_post") }}

                <i class="las la-arrow-right"></i>
            </a>
        </div>
    </div>
</div>