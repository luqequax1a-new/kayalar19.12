<nav class="modern-top-nav">
    <div class="nav-container">
        {{-- Left Section --}}
        <div class="nav-left">
            <button class="nav-mobile-hamburger" id="mobile-sidebar-toggle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <a href="{{ route('home') }}" target="_blank" class="store-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="nav-text">{{ trans('admin::admin.storefront') }}</span>
            </a>
        </div>

        {{-- Right Section --}}
        <div class="nav-right">
            @if (count(supported_locales()) > 1)
                <div class="nav-item dropdown" id="languageDropdown">
                    <button class="nav-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </button>
                    <div class="dropdown-menu">
                        @foreach (supported_locales() as $locale => $language)
                            <a href="{{ localized_url($locale) }}" class="dropdown-item {{ $locale === locale() ? 'active' : '' }}">
                                {{ $language['name'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Quick Actions Button --}}
            <div class="nav-item">
                <button class="quick-actions-btn" id="quickActionsBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="19" cy="12" r="1"></circle>
                        <circle cx="5" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                        <circle cx="5" cy="5" r="1"></circle>
                        <circle cx="19" cy="5" r="1"></circle>
                        <circle cx="5" cy="19" r="1"></circle>
                        <circle cx="19" cy="19" r="1"></circle>
                    </svg>
                    <span class="quick-actions-text">Hızlı İşlem</span>
                </button>
            </div>

            {{-- Notification Widget --}}
            <div class="nav-item">
                @include('admin::partials.notification_widget')
            </div>

            <div class="nav-item nav-desktop-only">
                <button class="nav-btn" id="fullscreenBtn">
                    <svg class="fullscreen-enter" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg>
                    <svg class="fullscreen-exit" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                    </svg>
                </button>
            </div>

            <div class="nav-divider"></div>

            <div class="nav-item dropdown" id="userDropdown">
                <button class="user-btn">
                    <div class="user-avatar">{{ substr($currentUser->first_name, 0, 1) }}</div>
                </button>
                <div class="dropdown-menu user-menu">
                    <div class="user-info">
                        <div class="user-avatar-large">{{ substr($currentUser->first_name, 0, 1) }}</div>
                        <div class="user-details">
                            <div class="user-name">{{ $currentUser->first_name }} {{ $currentUser->last_name }}</div>
                            <div class="user-role">{{ $currentUser->roles->first()->name }}</div>
                            <div class="user-email">{{ $currentUser->email }}</div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.profile.edit') }}" class="dropdown-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        {{ trans('user::users.profile') }}
                    </a>
                    <a href="{{ route('admin.logout') }}" class="dropdown-item logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        {{ trans('user::auth.logout') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
/* Modern Top Navigation Styling */
.modern-top-nav {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    height: 70px; /* Matched to sidebar header */
    position: sticky;
    top: 0;
    z-index: 99;
    width: 100%;
}

.nav-container {
    height: 100%;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.nav-left, .nav-right {
    display: flex;
    align-items: center;
}

.nav-mobile-hamburger {
    display: none;
    background: transparent;
    border: none;
    color: #475569;
    padding: 8px;
    margin-right: 12px;
    cursor: pointer;
}

.store-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border-radius: 10px;
    background: #f8fafc;
    color: #475569;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.store-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.nav-item {
    position: relative;
    margin-left: 8px;
}

.nav-btn {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    border: none;
    background: transparent;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.nav-btn:hover {
    background: #f8fafc;
    color: #0f172a;
}

.nav-divider {
    width: 1px;
    height: 24px;
    background: #e2e8f0;
    margin: 0 12px;
}

/* User Button */
.user-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
}

.user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #4f46e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.2s;
}

.user-btn:hover .user-avatar {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}

/* Dropdown Redesign */
.dropdown-menu {
    position: absolute !important;
    top: calc(100% + 12px) !important;
    right: 0 !important;
    left: auto !important;
    min-width: 240px !important;
    background: #fff !important;
    border-radius: 14px !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12), 0 5px 15px rgba(0,0,0,0.05) !important;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
    padding: 10px !important;
    display: none;
    z-index: 99999 !important;
    transform: none !important;
}

.dropdown.show .dropdown-menu {
    display: block;
    animation: navFadeUp 0.2s ease-out;
}

@keyframes navFadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    color: #475569;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background: #f8fafc;
    color: #0f172a;
}

.dropdown-item.active {
    background: #eff6ff;
    color: #2563eb;
}

.dropdown-item.logout {
    color: #ef4444;
    margin-top: 4px;
}

.dropdown-item.logout:hover {
    background: #fef2f2;
}

.dropdown-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 8px 0;
}

/* User Menu Specifics */
.user-menu {
    min-width: 280px !important;
}

.user-info {
    display: flex;
    gap: 15px;
    padding: 14px;
}

.user-avatar-large {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: #4f46e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 20px;
    flex-shrink: 0;
}

.user-name {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}

.user-role {
    font-size: 12px;
    color: #059669;
    background: #d1fae5;
    padding: 2px 10px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 5px;
    font-weight: 600;
}

.user-email {
    font-size: 13px;
    color: #64748b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Quick Actions Button */
.quick-actions-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 16px;
    border-radius: 8px;
    border: 1px solid #e3e8ef;
    background: #ffffff;
    color: #121926;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Twemoji Country Flags", Inter, sans-serif;
}

.quick-actions-btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.quick-actions-btn svg {
    color: #697586;
}

/* Quick Actions Modal - Full Screen */
.quick-actions-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

.quick-actions-modal.active {
    display: flex;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.quick-actions-content {
    background: #ffffff;
    border-radius: 16px;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.quick-actions-header {
    padding: 24px 28px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}

.quick-actions-title {
    font-size: 24px;
    font-weight: 600;
    color: #111827;
    margin: 0;
    font-family: "Twemoji Country Flags", Inter, sans-serif;
}

.quick-actions-close {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #6b7280;
    font-size: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.quick-actions-close:hover {
    background: #f3f4f6;
    color: #111827;
}

.quick-actions-body {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #ffffff;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}

.quick-action-card:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
}

.quick-action-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    transition: all 0.2s;
}

.quick-action-card:hover .quick-action-icon {
    transform: scale(1.05);
}

.quick-action-icon.blue {
    background: #eff6ff;
    color: #2563eb;
}

.quick-action-icon.green {
    background: #f0fdf4;
    color: #16a34a;
}

.quick-action-icon.purple {
    background: #faf5ff;
    color: #9333ea;
}

.quick-action-icon.orange {
    background: #fff7ed;
    color: #ea580c;
}

.quick-action-icon.pink {
    background: #fdf2f8;
    color: #ec4899;
}

.quick-action-icon.indigo {
    background: #eef2ff;
    color: #6366f1;
}

.quick-action-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin-bottom: 6px;
}

.quick-action-desc {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}

/* Responsive Handling */
@media (max-width: 991px) {
    .nav-text, .nav-desktop-only, .nav-divider {
        display: none !important;
    }
    .nav-container {
        padding: 0 15px;
    }
    .nav-mobile-hamburger {
        display: flex !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const langDropdown = document.getElementById('languageDropdown');
    const userDropdown = document.getElementById('userDropdown');

    if (langDropdown) {
        langDropdown.querySelector('.nav-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            langDropdown.classList.toggle('show');
            if (userDropdown) userDropdown.classList.remove('show');
        });
    }

    if (userDropdown) {
        userDropdown.querySelector('.user-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
            if (langDropdown) langDropdown.classList.remove('show');
        });
    }

    document.addEventListener('click', function() {
        if (langDropdown) langDropdown.classList.remove('show');
        if (userDropdown) userDropdown.classList.remove('show');
    });

    const fullscreenBtn = document.getElementById('fullscreenBtn');
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                this.classList.add('fullscreen');
            } else {
                document.exitFullscreen();
                this.classList.remove('fullscreen');
            }
        });

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                fullscreenBtn.classList.remove('fullscreen');
            }
        });
    }

    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            document.body.classList.toggle('sidebar-open');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 991 && document.body.classList.contains('sidebar-open')) {
            const sidebar = document.querySelector('.main-sidebar');
            if (sidebar && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                document.body.classList.remove('sidebar-open');
            }
        }
    });

    // Quick Actions Modal
    const quickActionsBtn = document.getElementById('quickActionsBtn');
    const quickActionsModal = document.getElementById('quickActionsModal');
    const quickActionsClose = document.getElementById('quickActionsClose');

    if (quickActionsBtn && quickActionsModal) {
        quickActionsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            quickActionsModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        if (quickActionsClose) {
            quickActionsClose.addEventListener('click', function() {
                quickActionsModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        quickActionsModal.addEventListener('click', function(e) {
            if (e.target === quickActionsModal) {
                quickActionsModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && quickActionsModal.classList.contains('active')) {
                quickActionsModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
});
</script>

{{-- Quick Actions Modal --}}
<div class="quick-actions-modal" id="quickActionsModal">
    <div class="quick-actions-content">
        <div class="quick-actions-header">
            <h2 class="quick-actions-title">Hızlı İşlemler</h2>
            <button class="quick-actions-close" id="quickActionsClose">×</button>
        </div>
        <div class="quick-actions-body">
            <div class="quick-actions-grid">
                {{-- Product Actions --}}
                <a href="{{ route('admin.products.create') }}" class="quick-action-card">
                    <div class="quick-action-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <div class="quick-action-title">Yeni Ürün Ekle</div>
                    <div class="quick-action-desc">Hızlıca yeni bir ürün oluşturun ve mağazanıza ekleyin</div>
                </a>

                {{-- Category Actions --}}
                <a href="{{ route('admin.categories.index') }}" class="quick-action-card">
                    <div class="quick-action-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <div class="quick-action-title">Kategoriler</div>
                    <div class="quick-action-desc">Ürün kategorilerini görüntüleyin ve yönetin</div>
                </a>

                {{-- Blog Post --}}
                @if(\Illuminate\Support\Facades\Route::has('admin.blog_posts.create'))
                <a href="{{ route('admin.blog_posts.create') }}" class="quick-action-card">
                    <div class="quick-action-icon purple">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="quick-action-title">Blog Yazısı Ekle</div>
                    <div class="quick-action-desc">Yeni bir blog yazısı oluşturun ve yayınlayın</div>
                </a>
                @endif

                {{-- Orders --}}
                <a href="{{ route('admin.orders.index') }}" class="quick-action-card">
                    <div class="quick-action-icon orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <div class="quick-action-title">Siparişleri Görüntüle</div>
                    <div class="quick-action-desc">Tüm siparişleri görüntüleyin ve yönetin</div>
                </a>

                {{-- Customers --}}
                <a href="{{ route('admin.users.index') }}" class="quick-action-card">
                    <div class="quick-action-icon pink">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="quick-action-title">Müşteriler</div>
                    <div class="quick-action-desc">Müşteri listesini görüntüleyin ve yönetin</div>
                </a>

                {{-- Coupons --}}
                <a href="{{ route('admin.coupons.create') }}" class="quick-action-card">
                    <div class="quick-action-icon indigo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline>
                            <polyline points="7.5 19.79 7.5 14.6 3 12"></polyline>
                            <polyline points="21 12 16.5 14.6 16.5 19.79"></polyline>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    <div class="quick-action-title">Kupon Oluştur</div>
                    <div class="quick-action-desc">Yeni indirim kuponu oluşturun</div>
                </a>

                {{-- Brand --}}
                @if(\Illuminate\Support\Facades\Route::has('admin.brands.create'))
                <a href="{{ route('admin.brands.create') }}" class="quick-action-card">
                    <div class="quick-action-icon blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </div>
                    <div class="quick-action-title">Marka Ekle</div>
                    <div class="quick-action-desc">Yeni bir marka oluşturun ve ürünlere atayın</div>
                </a>
                @endif

                {{-- Settings --}}
                @if(\Illuminate\Support\Facades\Route::has('admin.settings.edit'))
                <a href="{{ route('admin.settings.edit') }}" class="quick-action-card">
                    <div class="quick-action-icon green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M12 1v6m0 6v6m6-12h-6m-6 0H1m11 6H1m11 6H1"></path>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </div>
                    <div class="quick-action-title">Ayarlar</div>
                    <div class="quick-action-desc">Mağaza ayarlarını düzenleyin ve yapılandırın</div>
                </a>
                @endif

                {{-- Reports --}}
                @if(\Illuminate\Support\Facades\Route::has('admin.reports.index'))
                <a href="{{ route('admin.reports.index') }}" class="quick-action-card">
                    <div class="quick-action-icon purple">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <div class="quick-action-title">Raporlar</div>
                    <div class="quick-action-desc">Satış ve performans raporlarını görüntüleyin</div>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
