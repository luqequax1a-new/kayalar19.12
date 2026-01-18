<ul class="sidebar-more-menu-items">
    <li>
        <a href="{{ route('contact.create') }}">
            <div class="sidebar-icon-parent">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M17 20.5H7C4 20.5 2 19 2 15.5V8.5C2 5 4 3.5 7 3.5H17C20 3.5 22 5 22 8.5V15.5C22 19 20 20.5 17 20.5Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17 9L13.87 11.5C12.84 12.32 11.15 12.32 10.12 11.5L7 9" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <span>{{ trans('storefront::layouts.contact') }}</span>
        </a>
    </li>

    <li>
        <a href="{{ route('brands.index') }}">
            <div class="sidebar-icon-parent">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12.88V9.12C2 5.06 2 3.03 3.4 2.12C4.79 1.21 6.57 2.2 10.13 4.19L10.74 4.53C11.53 4.97 12.47 4.97 13.26 4.53L13.87 4.19C17.43 2.2 19.21 1.21 20.6 2.12C21.99 3.03 21.99 5.06 21.99 9.12V12.88C21.99 16.94 21.99 18.97 20.6 19.88C19.21 20.79 17.43 19.8 13.87 17.81L13.26 17.47C12.47 17.03 11.53 17.03 10.74 17.47L10.13 17.81C6.57 19.8 4.79 20.79 3.4 19.88C2 18.97 2 16.94 2 12.88Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <span>{{ trans('storefront::layouts.brands') }}</span>
        </a>
    </li>

    @if (setting('storefront_blogs_section_enabled'))
        <li>
            <a href="{{ route('blog_posts.index') }}">
                <div class="sidebar-icon-parent">
                    <svg class="blog-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g id="vuesax/linear/message-text">
                            <g id="message-text">
                                <path id="Vector" d="M8.5 19H8C4 19 2 18 2 13V8C2 4 4 2 8 2H16C20 2 22 4 22 8V13C22 17 20 19 16 19H15.5C15.19 19 14.89 19.15 14.7 19.4L13.2 21.4C12.54 22.28 11.46 22.28 10.8 21.4L9.3 19.4C9.14 19.18 8.77 19 8.5 19Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path id="Vector_2" d="M7 8H17" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path id="Vector_3" d="M7 13H13" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                        </g>
                    </svg>
                </div>

                <span>{{ trans('storefront::layouts.blog') }}</span>
            </a>
        </li>
    @endif
</ul>
