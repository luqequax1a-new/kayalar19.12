<aside class="main-sidebar ikas-sidebar">
    <div class="sidebar-wrapper">
        {{-- Header Area: Real Logo & Toggle --}}
        <header class="sidebar-header">
            <div class="branding-area" id="sidebar-logo-click">
                <a href="{{ route('admin.dashboard.index') }}" class="logo-link">
                    @if (is_null($logo))
                        <img src="{{ asset('build/assets/sidebar-logo-' . (is_rtl() ? 'rtl' : 'ltr') . '.svg') }}" alt="logo" class="full-logo">
                    @else
                        <img src="{{ $logo }}" alt="logo" class="full-logo">
                    @endif
                    <img src="{{ $smallLogo ?: asset('build/assets/sidebar-logo-mini.svg') }}" alt="logo" class="mini-logo">
                </a>
            </div>
            
            <button type="button" class="ikas-toggle" id="ikas-sidebar-toggle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <polyline points="16 10 14 12 16 14"></polyline>
                </svg>
            </button>
        </header>

        {{-- Navigation Area --}}
        <div class="sidebar-scroller">
            <nav class="ikas-nav">
                {!! $sidebar !!}
            </nav>
        </div>

        {{-- Footer Area (Interactive Profile with Dropup) --}}
        <footer class="sidebar-footer">
            <div class="user-profile-container" id="profile-container">
                <div class="user-profile-trigger">
                    <div class="user-avatar-circle">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="user-info">
                        @php
                            $userName = auth()->user()->full_name ?? 'ADMIN';
                            $storeName = setting('store_name') ?? 'mağaza';
                        @endphp
                        <span class="user-name">{{ strtoupper($userName) }}</span>
                        <span class="store-name">{{ strtolower($storeName) }}</span>
                    </div>
                    <div class="user-action" id="profile-dots-trigger">
                        <svg width="4" height="16" viewBox="0 0 4 16" fill="currentColor">
                            <circle cx="2" cy="2" r="2"></circle>
                            <circle cx="2" cy="8" r="2"></circle>
                            <circle cx="2" cy="14" r="2"></circle>
                        </svg>
                    </div>
                </div>

                {{-- Profile Dropup Menu --}}
                <div class="profile-dropup" id="profile-dropup-menu">
                    <div class="dropup-header">
                        <span class="dropup-title">{{ auth()->user()->full_name }}</span>
                        <span class="dropup-email">{{ auth()->user()->email }}</span>
                    </div>
                    <div class="dropup-divider"></div>
                    <a href="{{ route('admin.profile.edit') }}" class="dropup-item">
                        <i class="fa fa-user-circle-o"></i>
                        <span>{{ trans('user::users.profile') }}</span>
                    </a>
                    <a href="{{ route('admin.dashboard.index') }}" class="dropup-item">
                        <i class="fa fa-dashboard"></i>
                        <span>{{ trans('admin::dashboard.dashboard') }}</span>
                    </a>
                    <div class="dropup-divider"></div>
                    <a href="{{ route('admin.logout') }}" class="dropup-item logout-item">
                        <i class="fa fa-sign-out"></i>
                        <span>{{ trans('user::auth.logout') }}</span>
                    </a>
                </div>
            </div>
        </footer>
    </div>

    <style>
        /* --------------------------------------------------------- */
        /* IKAS ADMIN SIDEBAR REDESIGN - PIXEL PERFECT V5            */
        /* --------------------------------------------------------- */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        :root {
            --ikas-sidebar-width: 250px;
            --ikas-sidebar-collapsed: 72px;
            --ikas-bg-primary: #000000;
            --ikas-text-default: #ececed;
            --ikas-text-active: #f8fafc;
            --ikas-text-muted: #8e8e93;
            --ikas-border: rgba(255, 255, 255, 0.08);
            --ikas-transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --ikas-item-height: 38px;
        }

        /* Essential Reset & Layout Fixes */
        body { overflow-x: hidden; margin: 0; padding: 0; }
        .left-side, .main-footer { display: none !important; }

        /* Scoped Sidebar Styling - No Backgrounds except the container */
        .main-sidebar.ikas-sidebar {
            height: 100vh !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: var(--ikas-sidebar-width) !important;
            background: var(--ikas-bg-primary) !important;
            z-index: 1050 !important;
            transition: width var(--ikas-transition) !important;
            font-family: "Twemoji Country Flags", "Inter", sans-serif !important;
            border-right: 1px solid var(--ikas-border) !important;
            overflow: visible !important;
        }

        /* --------------------------------------------------------- */
        /* AGGRESSIVE CLEAN RESET (NO DOTS, NO LINES, NO BACKGROUNDS) */
        /* --------------------------------------------------------- */
        .sidebar-menu, 
        .sidebar-menu li, 
        .sidebar-menu a,
        .sidebar-menu span,
        .treeview-menu,
        .treeview-menu li,
        .treeview-menu a,
        .treeview-menu span {
            background-color: transparent !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-radius: 0 !important;
        }

        /* Specifically neutralize the legacy style mentioned */
        .treeview-menu > li.active a span {
            background: transparent !important;
            display: inline-block !important;
            padding: 0 !important;
        }

        /* Kill AdminLTE Blue Dots and Lines */
        .sidebar-menu li:before, .sidebar-menu li:after,
        .treeview-menu li:before, .treeview-menu li:after,
        .sidebar-menu li a:before, .sidebar-menu li a:after,
        .treeview-menu li a:before, .treeview-menu li a:after {
            display: none !important;
            content: none !important;
            border: none !important;
        }

        .sidebar-wrapper { height: 100%; display: flex; flex-direction: column; width: 100%; }

        .sidebar-header {
            height: 64px !important;
            padding: 0 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-shrink: 0 !important;
            border-bottom: 1px solid var(--ikas-border) !important;
            background: var(--ikas-bg-primary) !important;
        }
        .branding-area { cursor: pointer; display: flex; align-items: center; }
        .full-logo { height: 26px !important; width: auto !important; position: relative; }
        .mini-logo { display: none; height: 30px; }

        .ikas-toggle {
            width: 30px; height: 30px; background: transparent !important; border: 1px solid var(--ikas-border) !important;
            border-radius: 6px; color: var(--ikas-text-muted); display: flex; align-items: center;
            justify-content: center; cursor: pointer; transition: 0.2s; padding: 0;
        }
        .ikas-toggle:hover { color: #fff; border-color: rgba(255,255,255,0.3) !important; }

        .sidebar-scroller { 
            flex: 1; 
            overflow-y: auto; 
            overflow-x: hidden; 
            padding: 12px 10px; 
            background: transparent !important;
            
            /* Hide scrollbar but keep functionality */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        .sidebar-scroller::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        
        .ikas-nav .sidebar-menu { list-style: none; margin: 0; padding: 0; }
        .ikas-nav .sidebar-menu .header, 
        .ikas-nav .sidebar-menu li.header,
        .ikas-nav .sidebar-menu .menu-title,
        .ikas-nav .sidebar-menu li.menu-title { display: none !important; }

        .sidebar-menu > li { margin-bottom: 2px; position: relative; }
        
        .sidebar-menu li > a { 
            height: var(--ikas-item-height);
            padding: 0 10px; 
            border-radius: 4px; 
            color: var(--ikas-text-default); 
            display: flex !important; 
            align-items: center; 
            font-size: 14px; 
            font-weight: 400; 
            text-decoration: none !important; 
            transition: color var(--ikas-transition);
            white-space: nowrap;
        }

        .sidebar-menu li > a i, 
        .sidebar-menu li > a .fa { 
            width: 20px; 
            text-align: center; 
            font-size: 16px;
            margin-right: 12px;
            color: inherit;
        }

        .sidebar-menu li > a:hover { color: var(--ikas-text-active) !important; background: transparent !important; }
        .sidebar-menu li.active > a { 
            color: var(--ikas-text-active) !important; 
            font-weight: 600 !important; 
            background: transparent !important;
        }

        /* Extreme Background Sanitation - target links specifically */
        .ikas-nav li a, 
        .ikas-nav li.active a,
        .ikas-nav li a:hover,
        .ikas-nav li a span {
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Multi-level Treeview Arrow Fix */
        .ikas-nav li.treeview > a .pull-right-container {
            display: flex !important;
            margin-left: auto !important;
            order: 3;
            align-items: center;
        }

        /* Cleanest Submenu Treeview Logic */
        .treeview-menu-container {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        .menu-open > .treeview-menu-container { grid-template-rows: 1fr; }

        .treeview-menu { 
            min-height: 0;
            list-style: none !important; 
            padding: 2px 0 !important; 
            margin: 0 !important;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-open > .treeview-menu-container > .treeview-menu { opacity: 1; }
        
        .treeview-menu > li > a { padding-left: 42px !important; font-size: 13.5px; }
        .treeview-menu .treeview-menu > li > a { padding-left: 58px !important; font-size: 13px; }
        
        /* Sub-item Icon Removal except Arrows */
        .treeview-menu li > a i:not(.fa-angle-left), 
        .treeview-menu li > a .fa:not(.fa-angle-left) { 
            display: none !important; 
        }

        .pull-right-container { transition: transform 0.3s; }
        .fa-angle-left { font-size: 12px !important; color: var(--ikas-text-muted); transition: transform 0.3s; }
        .menu-open > a .fa-angle-left { transform: rotate(-90deg); }

        /* Footer & Profile */
        .sidebar-footer { 
            padding: 12px; 
            border-top: 1px solid var(--ikas-border) !important;
            background: var(--ikas-bg-primary) !important;
            flex-shrink: 0;
        }
        .user-profile-trigger { display: flex; align-items: center; gap: 12px; padding: 4px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
        .user-profile-trigger:hover { background: rgba(255,255,255,0.05) !important; }
        
        .user-avatar-circle { 
            width: 32px; height: 32px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2) !important; 
            display: flex; align-items: center; justify-content: center; color: #fff; background: #333 !important; flex-shrink: 0;
        }
        .user-info { display: flex; flex-direction: column; overflow: hidden; }
        .user-name { color: #fff; font-size: 13px; font-weight: 600; white-space: nowrap; }
        .store-name { color: var(--ikas-text-muted); font-size: 11px; white-space: nowrap; }
        
        .profile-dropup {
            position: absolute; bottom: 70px; left: 10px; right: 10px;
            background: #1a1a1b !important; border: 1px solid var(--ikas-border) !important; border-radius: 8px;
            display: none; flex-direction: column; padding: 6px; z-index: 2000; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .profile-dropup.show { display: flex; }
        
        /* Collapsed sidebar - show dropdown on RIGHT side */
        body.sidebar-collapse .profile-dropup {
            left: auto !important;
            right: auto !important;
            bottom: 12px !important;
            margin-left: calc(var(--ikas-sidebar-collapsed) + 8px) !important;
            width: 200px !important;
        }
        
        .dropup-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; color: var(--ikas-text-default) !important; text-decoration: none; font-size: 13px; border-radius: 4px; }
        .dropup-item:hover { background: rgba(255,255,255,0.05) !important; color: #fff !important; }
        .dropup-item i { width: 16px; text-align: center; }

        /* Desktop Layout Fixes */
        @media (min-width: 992px) {
            .content-wrapper, .main-header { 
                margin-left: var(--ikas-sidebar-width) !important; 
                transition: margin-left var(--ikas-transition) !important; 
                width: auto !important;
            }
            body.sidebar-collapse .content-wrapper, 
            body.sidebar-collapse .main-header { margin-left: var(--ikas-sidebar-collapsed) !important; }
            body.sidebar-collapse .main-sidebar.ikas-sidebar { width: var(--ikas-sidebar-collapsed) !important; }
            
            /* Collapsed state - show mini logo, hide full logo and toggle */
            body.sidebar-collapse .full-logo { display: none !important; }
            body.sidebar-collapse .mini-logo { display: block !important; }
            body.sidebar-collapse .ikas-toggle { display: none !important; }
            
            /* Hide text in menu items */
            body.sidebar-collapse .sidebar-menu li > a span:not(.fa), 
            body.sidebar-collapse .pull-right-container, 
            body.sidebar-collapse .user-info { display: none !important; }
            body.sidebar-collapse .sidebar-menu li > a { justify-content: center; padding: 0 !important; }
            body.sidebar-collapse .sidebar-menu li > a i { margin-right: 0; }
            body.sidebar-collapse .treeview-menu-container { display: none !important; }
        }

        /* Mobile Layout fix */
        @media (max-width: 991px) {
            .main-sidebar.ikas-sidebar { left: calc(-1 * var(--ikas-sidebar-width)) !important; transition: left var(--ikas-transition) !important; }
            .sidebar-open .main-sidebar.ikas-sidebar { left: 0 !important; box-shadow: 10px 0 50px rgba(0,0,0,0.5); }
            .sidebar-open::after { content: ""; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1040; backdrop-filter: blur(2px); }
            .content-wrapper, .main-header { margin-left: 0 !important; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.body;
            const toggleBtn = document.getElementById('ikas-sidebar-toggle');
            const profileContainer = document.getElementById('profile-container');
            const profileMenu = document.getElementById('profile-dropup-menu');

            // 1. Wrap menus for animation
            document.querySelectorAll('.ikas-nav .treeview-menu').forEach(menu => {
                if (!menu.parentElement.classList.contains('treeview-menu-container')) {
                    const container = document.createElement('div');
                    container.className = 'treeview-menu-container';
                    menu.parentNode.insertBefore(container, menu);
                    container.appendChild(menu);
                    menu.style.display = 'block';
                }
            });

            // 2. Initialize active items - ensure parent of active items are open
            document.querySelectorAll('.sidebar-menu li.active').forEach(li => {
                li.classList.add('menu-open');
                let parent = li.parentElement.closest('li.treeview');
                while(parent) {
                    parent.classList.add('menu-open');
                    parent = parent.parentElement.closest('li.treeview');
                }
            });

            // 3. Inject arrows for nested treeviews ONLY if completely missing
            document.querySelectorAll('.ikas-nav li.treeview > a').forEach(link => {
                const hasContainer = link.querySelector('.pull-right-container');
                const hasIcon = link.querySelector('.fa-angle-left');
                
                if (!hasContainer && !hasIcon) {
                    const span = document.createElement('span');
                    span.className = 'pull-right-container';
                    span.innerHTML = '<i class="fa fa-angle-left pull-right"></i>';
                    link.appendChild(span);
                }
            });

            function toggleSidebar() {
                if (window.innerWidth > 991) {
                    body.classList.toggle('sidebar-collapse');
                    localStorage.setItem('sidebar_collapsed', body.classList.contains('sidebar-collapse'));
                } else {
                    body.classList.toggle('sidebar-open');
                }
                setTimeout(() => window.dispatchEvent(new Event('resize')), 250);
            }

            if (toggleBtn) toggleBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleSidebar(); });
            
            // Profile click - always show dropdown menu (both collapsed and expanded)
            const profileTrigger = document.querySelector('.user-profile-trigger');
            if (profileTrigger && profileMenu) {
                profileTrigger.addEventListener('click', (e) => { 
                    e.preventDefault();
                    e.stopPropagation(); 
                    
                    // Always toggle the profile menu (don't expand sidebar)
                    profileMenu.classList.toggle('show');
                });
            }
            
            // Logo click - if collapsed, open sidebar instead of going to dashboard
            const logoLink = document.querySelector('.logo-link');
            if (logoLink) {
                logoLink.addEventListener('click', (e) => {
                    if (body.classList.contains('sidebar-collapse') && window.innerWidth > 991) {
                        e.preventDefault();
                        e.stopPropagation();
                        toggleSidebar();
                    }
                    // If not collapsed, allow normal link behavior (go to dashboard)
                });
            }
            
            document.addEventListener('click', () => { if (profileMenu) profileMenu.classList.remove('show'); });

            // Submenu Toggle - Fixed to allow closing active items
            document.querySelectorAll('.ikas-nav .treeview > a').forEach(link => {
                link.addEventListener('click', function(e) {
                    if (body.classList.contains('sidebar-collapse') && window.innerWidth > 991) return;
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const parent = this.parentElement;
                    const isOpen = parent.classList.contains('menu-open');
                    
                    // Close siblings
                    parent.parentElement.querySelectorAll(':scope > .treeview.menu-open').forEach(opened => {
                        if (opened !== parent) opened.classList.remove('menu-open');
                    });

                    // Toggle self
                    parent.classList.toggle('menu-open');
                });
            });

            if (localStorage.getItem('sidebar_collapsed') === 'true' && window.innerWidth > 991) body.classList.add('sidebar-collapse');
            document.body.addEventListener('click', function(e) {
                if (body.classList.contains('sidebar-open') && !e.target.closest('.main-sidebar')) body.classList.remove('sidebar-open');
            }, true);
        });
    </script>
</aside>
