@php
    $serverReviewCount = null;
    if (isset($data)) {
        if (is_object($data)) {
            $serverReviewCount = $data->reviews_count ?? 0;
        } elseif (is_array($data)) {
            $serverReviewCount = $data['reviews_count'] ?? 0;
        }
    }
    $serverHasReviews = is_int($serverReviewCount) ? ($serverReviewCount > 0) : null;
@endphp

@php
     $xDataParam = is_null($data ?? null)
         ? 'product'
         : (is_string($data ?? null)
             ? ($data ?? 'product')
             : json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));
@endphp

<div x-data="ProductRating({{ $xDataParam }})">
    <a
        class="product-rating {{ $serverHasReviews === null ? '' : ($serverHasReviews ? 'has-reviews' : 'no-reviews') }}"
        @if ($serverHasReviews === null)
            :class="{ 'no-reviews': !(reviewCount > 0) }"
        @endif
        :href="(typeof productUrl !== 'undefined' ? productUrl : window.location.pathname) + '#reviews'"
        x-on:click.prevent="typeof openReviewsTab==='function' && openReviewsTab()"
    >
        <div class="back-stars">
            <i class="las la-star"></i>
            <i class="las la-star"></i>
            <i class="las la-star"></i>
            <i class="las la-star"></i>
            <i class="las la-star"></i>

            <div x-cloak class="front-stars" :style="{ width: ratingPercent + '%' }">
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>
                <i class="las la-star"></i>
            </div>
        </div>

        <template x-if="reviewCount > 0">
            <span class="rating-count" x-text="'(' + reviewCount + ')' "></span>
        </template>
    </a>
</div>
