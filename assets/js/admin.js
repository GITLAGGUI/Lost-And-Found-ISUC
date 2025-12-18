document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Reveal animations when panels enter viewport
    const animatedNodes = document.querySelectorAll('[data-animate]');
    if (animatedNodes.length && !prefersReducedMotion) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        animatedNodes.forEach(node => observer.observe(node));
    } else {
        animatedNodes.forEach(node => node.classList.add('is-visible'));
    }

    // Light-weight number animation for metrics
    const metrics = document.querySelectorAll('[data-metric]');
    metrics.forEach(el => {
        const suffix = el.dataset.suffix || '';
        const targetValue = Number(el.dataset.target || 0);
        if (!Number.isFinite(targetValue) || prefersReducedMotion) {
            el.textContent = `${targetValue}${suffix}`;
            return;
        }
        let start = null;
        const duration = 700;
        const initial = 0;

        function step(timestamp) {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const value = Math.floor(progress * (targetValue - initial) + initial);
            el.textContent = `${value}${suffix}`;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = `${targetValue}${suffix}`;
            }
        }

        requestAnimationFrame(step);
    });

    // Queue toggle logic
    const queueButtons = document.querySelectorAll('[data-queue-target]');
    const queuePanels = document.querySelectorAll('[data-queue-panel]');

    queueButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.queueTarget;
            queueButtons.forEach(b => b.classList.toggle('is-active', b === btn));
            queuePanels.forEach(panel => {
                const isMatch = panel.dataset.queuePanel === target;
                panel.hidden = !isMatch;
            });
        });
    });

    // Admin Sidebar Mobile Toggle
    const adminSidebarToggle = document.querySelector('[data-js="admin-sidebar-toggle"]');
    const adminSidebar = document.querySelector('[data-js="admin-sidebar"]');
    const adminSidebarOverlay = document.querySelector('[data-js="admin-sidebar-overlay"]');

    function openAdminSidebar() {
        if (!adminSidebar || !adminSidebarToggle) return;
        adminSidebar.classList.add('is-visible');
        adminSidebarOverlay?.classList.add('is-visible');
        adminSidebarToggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminSidebar() {
        if (!adminSidebar || !adminSidebarToggle) return;
        adminSidebar.classList.remove('is-visible');
        adminSidebarOverlay?.classList.remove('is-visible');
        adminSidebarToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    function toggleAdminSidebar() {
        if (adminSidebar?.classList.contains('is-visible')) {
            closeAdminSidebar();
        } else {
            openAdminSidebar();
        }
    }

    if (adminSidebarToggle && adminSidebar) {
        adminSidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleAdminSidebar();
        });

        adminSidebarOverlay?.addEventListener('click', closeAdminSidebar);

        // Close when clicking a link in the sidebar (mobile)
        adminSidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 960) {
                    closeAdminSidebar();
                }
            });
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && adminSidebar.classList.contains('is-visible')) {
                closeAdminSidebar();
                adminSidebarToggle.focus();
            }
        });

        // Handle resize - close if resized to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 960 && adminSidebar.classList.contains('is-visible')) {
                closeAdminSidebar();
            }
        });
        
        // Touch swipe to close sidebar (mobile UX improvement)
        let touchStartX = 0;
        let touchEndX = 0;
        
        adminSidebar.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });
        
        adminSidebar.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const swipeDistance = touchStartX - touchEndX;
            
            // Swipe left to close (since sidebar comes from left)
            if (swipeDistance > swipeThreshold && adminSidebar.classList.contains('is-visible')) {
                closeAdminSidebar();
            }
        }
    }

    // Sidebar navigation - smooth scroll for same-page anchors
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    const sections = document.querySelectorAll('section[id]');

    function updateActiveLink() {
        const scrollY = window.scrollY;
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');

            if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                sidebarLinks.forEach(link => {
                    link.classList.remove('is-active');
                    if (link.getAttribute('href') === `#${sectionId}`) {
                        link.classList.add('is-active');
                    }
                });
            }
        });
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const targetId = href.substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    const offsetTop = targetSection.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: prefersReducedMotion ? 'auto' : 'smooth'
                    });
                }
            }
            // If not starting with #, allow default navigation
        });
    });

    window.addEventListener('scroll', updateActiveLink);
    updateActiveLink(); // Initial check
    
    // Add haptic feedback for mobile (if supported)
    const actionButtons = document.querySelectorAll('.queue-actions .btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (navigator.vibrate) {
                navigator.vibrate(10); // Short haptic feedback
            }
        });
    });
});
