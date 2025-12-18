<?php 
require_once __DIR__ . '/functions.php';
$current_user = get_logged_in_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' · ' : ''; ?><?php echo APP_NAME; ?></title>
    <base href="<?php echo app_url(); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo app_url('assets/css/base.css'); ?>" />
    <link rel="stylesheet" href="<?php echo app_url('assets/css/style.css'); ?>" />
    <link rel="stylesheet" href="<?php echo app_url('assets/css/components.css'); ?>" />
    <link rel="stylesheet" href="<?php echo app_url('assets/css/pages.css'); ?>" />
    <link rel="stylesheet" href="<?php echo app_url('assets/css/admin.css'); ?>" />
    
    <!-- Mobile Menu Styles - Override Everything -->
    <style id="mobile-menu-styles">
        /* Hamburger Button - Always visible on mobile */
        .mobile-menu-btn {
            display: none;
            width: 48px;
            height: 48px;
            padding: 12px;
            background: transparent;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            z-index: 10001;
            position: relative;
        }
        
        .mobile-menu-btn:hover {
            background: rgba(5, 150, 105, 0.1);
        }
        
        .mobile-menu-btn span {
            display: block;
            width: 24px;
            height: 3px;
            background: #047857;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        /* Hamburger to X animation */
        .mobile-menu-btn.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }
        
        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }
        
        .mobile-menu-btn.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
        
        /* Global Header Hidden State */
        .site-header.header-hidden {
            transform: translateY(-100%);
        }
        
        /* Overlay */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .mobile-menu-overlay.active {
            opacity: 1;
        }
        
        /* Mobile breakpoint */
        @media (max-width: 768px) {
            :root {
                --mobile-header-offset: 86px;
            }

            /* Fixed header on mobile with hide on scroll down */
            .site-header {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                z-index: 10000 !important;
                background: rgba(255, 255, 255, 0.98) !important;
                transition: transform 0.3s ease !important;
            }
            
            .site-header.header-hidden {
                transform: translateY(-100%) !important;
            }
            
            body {
                padding-top: var(--mobile-header-offset, 86px) !important;
            }
            
            /* Ensure main content has proper spacing below fixed header */
            #main-content {
                padding-top: clamp(1.5rem, 4.5vw, 2.5rem) !important;
                min-height: calc(100vh - var(--mobile-header-offset, 86px));
            }
            
            /* Admin pages need different handling - layout provides its own padding */
            #main-content.admin-main {
                padding-top: clamp(1rem, 4vw, 1.5rem) !important;
            }
            
            /* First section header gets extra top margin */
            #main-content > .section-header:first-child,
            #main-content > .hero:first-child,
            #main-content > .page-hero:first-child,
            #main-content > .profile-hero:first-child {
                padding-top: calc(var(--mobile-header-offset, 86px) * 0.55) !important;
                scroll-margin-top: calc(var(--mobile-header-offset, 86px) + 24px);
            }

            /* Dashboard card grid spacing */
            #main-content > .card-grid:first-of-type {
                margin-top: 0.5rem;
            }
            
            .mobile-menu-btn {
                display: flex !important;
            }
            
            .mobile-menu-overlay {
                display: block;
                pointer-events: none;
            }
            
            .mobile-menu-overlay.active {
                pointer-events: auto;
            }
            
            /* Hide the default nav on mobile */
            .site-nav {
                position: fixed !important;
                top: 0 !important;
                right: -320px !important;
                width: 300px !important;
                max-width: 85vw !important;
                height: 100vh !important;
                background: #ffffff !important;
                flex-direction: column !important;
                padding: 80px 20px 30px !important;
                box-shadow: -5px 0 30px rgba(0,0,0,0.2) !important;
                z-index: 9999 !important;
                transition: right 0.3s ease !important;
                overflow-y: auto !important;
                gap: 8px !important;
            }
            
            .site-nav.mobile-open {
                right: 0 !important;
            }
            
            .site-nav::before {
                content: 'Menu';
                position: absolute;
                top: 25px;
                left: 20px;
                font-size: 14px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2px;
                color: #047857;
            }
            
            .site-nav a:not(.btn) {
                display: flex !important;
                align-items: center;
                gap: 12px;
                padding: 14px 16px !important;
                width: 100% !important;
                border-radius: 10px !important;
                font-size: 16px !important;
                color: #1a1a1a !important;
                background: transparent !important;
                border: 1px solid transparent !important;
                transition: all 0.2s ease !important;
            }
            
            .site-nav a:not(.btn):hover,
            .site-nav a:not(.btn):active {
                background: rgba(5, 150, 105, 0.08) !important;
                border-color: rgba(5, 150, 105, 0.15) !important;
                color: #047857 !important;
            }
            
            .site-nav a:not(.btn) svg {
                display: block !important;
                flex-shrink: 0;
                color: #059669;
            }
            
            .site-nav .btn {
                width: 100% !important;
                justify-content: center !important;
                padding: 14px 20px !important;
                margin-top: 8px !important;
            }
            
            .site-nav .user-menu {
                width: 100%;
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid #e5e7eb;
            }
            
            .site-nav .user-menu-toggle {
                width: 100%;
                justify-content: space-between;
            }
            
            /* Hide old nav-toggle */
            .nav-toggle {
                display: none !important;
            }
            
            .nav-overlay {
                display: none !important;
            }
        }
        
        /* Desktop - hide mobile button */
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none !important;
            }
            
            .mobile-menu-overlay {
                display: none !important;
            }
        }
    </style>
</head>
<body<?php echo isset($bodyClass) ? ' class="' . e($bodyClass) . '"' : ''; ?>>
<a href="#main-content" class="skip-nav">Skip to main content</a>
<?php if ($flash): ?>
<div class="flash-message flash-<?php echo e($flash['type']); ?>" data-js="flash">
    <?php echo e($flash['message']); ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>
<header class="site-header">
    <div class="header-container">
        <a href="<?php echo app_url('index.php'); ?>" class="logo">
            <img src="<?php echo app_url('assets/images/logo.png'); ?>" alt="ISU Logo" class="logo-mark">
            <div class="logo-text">
                <strong><?php echo APP_NAME; ?></strong>
                <small><?php echo APP_TAGLINE; ?></small>
            </div>
            <span class="logo-mobile-text">ISU Lost & Found</span>
        </a>
        
        <!-- New Mobile Menu Button -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Overlay -->
        <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
        
        <nav class="site-nav" id="siteNav">
        <a href="<?php echo app_url('index.php'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Home
        </a>
        <a href="<?php echo app_url('listings.php'); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            Browse
        </a>
        <?php if (is_logged_in()): ?>
            <a href="<?php echo app_url('dashboard.php'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                Dashboard
            </a>
            <?php if (is_admin()): ?>
                <a href="<?php echo app_url('admin/index.php'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    Admin
                </a>
            <?php endif; ?>
            <div class="user-menu">
                <button class="btn ghost user-menu-toggle" data-js="user-menu-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?php echo e($current_user['username']); ?> ▾
                </button>
                <div class="user-menu-dropdown" data-js="user-menu-dropdown">
                    <a href="<?php echo app_url('profile.php'); ?>">Profile settings</a>
                    <a href="<?php echo app_url('logout.php'); ?>">Log out</a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?php echo app_url('register.php'); ?>" class="btn ghost">Register</a>
            <a href="<?php echo app_url('login.php'); ?>" class="btn primary">Log in</a>
        <?php endif; ?>
        </nav>
    </div>
</header>
<main id="main-content"<?php echo isset($mainClass) ? ' class="' . e($mainClass) . '"' : ''; ?>>

<!-- Mobile Menu Script -->
<script>
(function() {
    const menuBtn = document.getElementById('mobileMenuBtn');
    const siteNav = document.getElementById('siteNav');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (!menuBtn || !siteNav) return;
    
    function openMenu() {
        menuBtn.classList.add('active');
        siteNav.classList.add('mobile-open');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeMenu() {
        menuBtn.classList.remove('active');
        siteNav.classList.remove('mobile-open');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function toggleMenu() {
        if (siteNav.classList.contains('mobile-open')) {
            closeMenu();
        } else {
            openMenu();
        }
    }
    
    // Toggle on button click
    menuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu();
    });
    
    // Close on overlay click
    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }
    
    // Close on link click
    siteNav.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeMenu();
            }
        });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMenu();
        }
    });
    
    // Close on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMenu();
        }
    });
    
    // Hide header on scroll down, show on scroll up (mobile only)
    let lastScrollY = window.scrollY;
    let ticking = false;
    const header = document.querySelector('.site-header');
    
    function updateHeader() {
        const currentScrollY = window.scrollY;
        
        // Apply on all screen sizes
        // Scrolling down - hide header (but not if menu is open)
        if (currentScrollY > lastScrollY && currentScrollY > 60 && !siteNav.classList.contains('mobile-open')) {
            header.classList.add('header-hidden');
        } 
        // Scrolling up - show header
        else if (currentScrollY < lastScrollY) {
            header.classList.remove('header-hidden');
        }
        
        lastScrollY = currentScrollY;
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(updateHeader);
            ticking = true;
        }
    });
})();
</script>
