// Mobile Navigation Toggle
const navToggle = document.querySelector('[data-js="nav-toggle"]');
const navMenu = document.querySelector('[data-js="site-nav"]');
const navOverlay = document.querySelector('[data-js="nav-overlay"]');

function openMobileNav() {
    if (!navMenu || !navToggle) return;
    navMenu.classList.add('is-visible');
    navOverlay?.classList.add('is-visible');
    navToggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
    document.body.style.touchAction = 'none';
}

function closeMobileNav() {
    if (!navMenu || !navToggle) return;
    navMenu.classList.remove('is-visible');
    navOverlay?.classList.remove('is-visible');
    navToggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    document.body.style.touchAction = '';
}

function toggleMobileNav() {
    if (navMenu?.classList.contains('is-visible')) {
        closeMobileNav();
    } else {
        openMobileNav();
    }
}

if (navToggle && navMenu) {
    // Toggle menu on hamburger click
    navToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMobileNav();
    });
    
    // Close menu when clicking overlay
    navOverlay?.addEventListener('click', closeMobileNav);
    
    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navMenu.contains(e.target) && !navToggle.contains(e.target) && navMenu.classList.contains('is-visible')) {
            closeMobileNav();
        }
    });
    
    // Close menu when clicking on a link (mobile)
    navMenu.querySelectorAll('a:not(.user-menu-toggle)').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeMobileNav();
            }
        });
    });
    
    // Close menu on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navMenu.classList.contains('is-visible')) {
            closeMobileNav();
            navToggle.focus();
        }
    });
    
    // Handle window resize - close menu if resized to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768 && navMenu.classList.contains('is-visible')) {
            closeMobileNav();
        }
    });
}

// User menu dropdown
const userMenuToggle = document.querySelector('[data-js="user-menu-toggle"]');
const userMenuDropdown = document.querySelector('[data-js="user-menu-dropdown"]');

if (userMenuToggle && userMenuDropdown) {
    userMenuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        userMenuDropdown.classList.toggle('is-visible');
    });

    document.addEventListener('click', (e) => {
        if (!userMenuDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
            userMenuDropdown.classList.remove('is-visible');
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            userMenuDropdown.classList.remove('is-visible');
        }
    });
}

// Auto-hide flash messages
const flashMessages = document.querySelectorAll('[data-js="flash"]');
flashMessages.forEach(flash => {
    setTimeout(() => {
        flash.style.animation = 'slideOutRight 0.3s ease forwards';
        setTimeout(() => flash.remove(), 300);
    }, 5000);
});

const listingSearch = document.querySelector('[data-js="listing-search"]');
const listingCategory = document.querySelector('[data-js="listing-category"]');
const listingStatus = document.querySelector('[data-js="listing-status"]');
const listingCards = document.querySelectorAll('[data-js="listing-card"]');

function applyFilters() {
    const query = listingSearch ? listingSearch.value.trim().toLowerCase() : '';
    const category = listingCategory ? listingCategory.value : '';
    const status = listingStatus ? listingStatus.value : '';

    listingCards.forEach(card => {
        const text = card.dataset.text;
        const cardCategory = card.dataset.category;
        const cardStatus = card.dataset.status;

        const matchesQuery = !query || text.includes(query);
        const matchesCategory = !category || cardCategory === category;
        const matchesStatus = !status || cardStatus === status;

        card.style.display = matchesQuery && matchesCategory && matchesStatus ? 'flex' : 'none';
    });
}

[listingSearch, listingCategory, listingStatus].forEach(input => {
    if (input) {
        input.addEventListener('input', applyFilters);
    }
});

const tabButtons = document.querySelectorAll('[data-tab-target]');
const tabPanels = document.querySelectorAll('[data-tab-panel]');

// Function to switch to a specific tab
function switchToTab(tabName) {
    tabButtons.forEach(b => b.classList.remove('active'));
    tabPanels.forEach(panel => {
        if (panel.dataset.tabPanel === tabName) {
            panel.hidden = false;
        } else {
            panel.hidden = true;
        }
    });
    
    // Activate the correct button
    tabButtons.forEach(btn => {
        if (btn.dataset.tabTarget === tabName) {
            btn.classList.add('active');
        }
    });
}

// Handle tab parameter from URL on page load
const urlParams = new URLSearchParams(window.location.search);
const tabParam = urlParams.get('tab');
if (tabParam && (tabParam === 'lost' || tabParam === 'found')) {
    switchToTab(tabParam);
}

// Also handle hash in URL (e.g., #found)
if (window.location.hash === '#found') {
    switchToTab('found');
} else if (window.location.hash === '#lost') {
    switchToTab('lost');
}

tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.tabTarget;
        switchToTab(target);
    });
});

// Image preview before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview) return;

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" />`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        const formGroup = input.closest('.form-group');
        const errorEl = formGroup ? formGroup.querySelector('.form-error') : null;

        if (!input.value.trim()) {
            isValid = false;
            if (formGroup) formGroup.classList.add('has-error');
            if (errorEl) errorEl.textContent = 'This field is required';
        } else {
            if (formGroup) formGroup.classList.remove('has-error');
            if (errorEl) errorEl.textContent = '';
        }
    });

    return isValid;
}

// =============================================
// MOBILE UX ENHANCEMENTS
// =============================================

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const target = document.querySelector(targetId);
        if (target) {
            e.preventDefault();
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Handle viewport height on mobile (fix for address bar)
function setViewportHeight() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
}
setViewportHeight();
window.addEventListener('resize', setViewportHeight);

// Add loading state to buttons on form submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';
            submitBtn.dataset.originalText = originalText;
            
            // Re-enable after timeout (fallback)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 10000);
        }
    });
});

// Improve touch feedback
if ('ontouchstart' in window) {
    document.querySelectorAll('.btn, .item-card, .stat-card, .tab').forEach(el => {
        el.addEventListener('touchstart', function() {
            this.style.opacity = '0.8';
        }, { passive: true });
        
        el.addEventListener('touchend', function() {
            this.style.opacity = '';
        }, { passive: true });
        
        el.addEventListener('touchcancel', function() {
            this.style.opacity = '';
        }, { passive: true });
    });
}

// Auto-resize textarea
document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 300) + 'px';
    });
});

// Focus styling enhancement for accessibility
document.querySelectorAll('input, select, textarea, button, a').forEach(el => {
    el.addEventListener('focus', function() {
        this.closest('.form-group, .item-card, .stat-card')?.classList.add('is-focused');
    });
    
    el.addEventListener('blur', function() {
        this.closest('.form-group, .item-card, .stat-card')?.classList.remove('is-focused');
    });
});

// =============================================
// MODAL FUNCTIONALITY
// =============================================

// Modal elements
const itemModal = document.getElementById('item-modal');
const modalOverlay = document.querySelector('[data-js="modal-overlay"]');
const modalClose = document.querySelector('[data-js="modal-close"]');
const modalTitle = document.getElementById('modal-title');
const modalBody = document.getElementById('modal-body');

// Show item details in modal
function showItemDetails(itemId, type) {
    if (!itemModal || !modalTitle || !modalBody) return;
    
    // Show loading state
    modalTitle.textContent = 'Loading...';
    modalBody.innerHTML = '<div style="text-align: center; padding: 2rem;"><div class="loading-spinner"></div><p>Loading item details...</p></div>';
    itemModal.hidden = false;
    document.body.style.overflow = 'hidden';
    
    // Fetch item details via AJAX
    fetch(`api/get_item_details.php?id=${itemId}&type=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const isLost = type === 'lost';
                
                modalTitle.textContent = `${isLost ? 'Lost' : 'Found'}: ${item.item_name}`;
                
                modalBody.innerHTML = `
                    <div class="item-detail-media">
                        ${item.image_path ? 
                            `<img src="${item.image_path}" alt="Photo of ${item.item_name}" onclick="toggleImageZoom(this)" />
                            <div class="zoom-controls">
                                <button class="zoom-btn" onclick="zoomImage(this, 'in')" title="Zoom In">+</button>
                                <button class="zoom-btn" onclick="zoomImage(this, 'out')" title="Zoom Out">−</button>
                            </div>` :
                            `<div class="item-media-fallback" style="width: 200px; height: 200px; margin: 0 auto;">
                                <span class="icon-circle" aria-hidden="true">📷</span>
                                <span>No photo available</span>
                            </div>`
                        }
                    </div>
                    
                    <div class="item-detail-content">
                        ${item.status ? `<div style="margin-bottom: 1rem;">${item.status}</div>` : ''}
                        <h3>${item.item_name}</h3>
                        <div class="item-detail-meta">
                            <div>
                                <strong>${isLost ? 'Date Lost' : 'Date Found'}</strong>
                                <span>${item.date_formatted}</span>
                            </div>
                            <div>
                                <strong>Location</strong>
                                <span>${item.location}</span>
                            </div>
                            <div>
                                <strong>Category</strong>
                                <span>${item.category}</span>
                            </div>
                            <div>
                                <strong>Posted by</strong>
                                <span>${item.username}</span>
                            </div>
                        </div>
                        
                        <div class="item-detail-description">
                            <strong>Description:</strong><br>
                            ${item.description || 'No description provided.'}
                        </div>
                        
                        ${(item.contact_email || item.contact_phone) ? `
                        <div class="item-detail-contact">
                            <h4>Contact Information</h4>
                            <div class="contact-chips">
                                ${item.contact_email ? 
                                    `<a href="mailto:${item.contact_email}" class="contact-chip">
                                        <span class="chip-icon" aria-hidden="true">@</span>
                                        Email ${isLost ? 'owner' : 'finder'}
                                    </a>` : ''
                                }
                                ${item.contact_phone ? 
                                    `<span class="contact-chip">
                                        <span class="chip-icon" aria-hidden="true">☎</span>
                                        ${item.contact_phone}
                                    </span>` : ''
                                }
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
            } else {
                modalTitle.textContent = 'Error';
                modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--clr-error);"><p>Failed to load item details. Please try again.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading item details:', error);
            modalTitle.textContent = 'Error';
            modalBody.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--clr-error);"><p>Failed to load item details. Please try again.</p></div>';
        });
}

// Hide modal
function hideModal() {
    if (!itemModal) return;
    
    // Reset any zoomed images
    const zoomedImages = itemModal.querySelectorAll('.item-detail-media img.zoomed');
    zoomedImages.forEach(img => {
        img.classList.remove('zoomed');
        img.style.transform = '';
    });
    
    itemModal.hidden = true;
    document.body.style.overflow = '';
}

// Modal event listeners
if (modalOverlay) {
    modalOverlay.addEventListener('click', hideModal);
}

if (modalClose) {
    modalClose.addEventListener('click', hideModal);
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && itemModal && !itemModal.hidden) {
        hideModal();
    }
});

// =============================================
// IMAGE ZOOM FUNCTIONALITY
// =============================================

// Toggle image zoom on click
function toggleImageZoom(img) {
    img.classList.toggle('zoomed');
}

// Zoom image using buttons
function zoomImage(button, direction) {
    const mediaContainer = button.closest('.item-detail-media');
    const img = mediaContainer.querySelector('img');
    
    if (!img) return;
    
    const currentScale = img.classList.contains('zoomed') ? 1.5 : 1;
    let newScale;
    
    if (direction === 'in') {
        newScale = Math.min(currentScale * 1.2, 3); // Max zoom 3x
    } else {
        newScale = Math.max(currentScale / 1.2, 0.5); // Min zoom 0.5x
    }
    
    if (newScale === 1) {
        img.classList.remove('zoomed');
        img.style.transform = '';
    } else {
        img.classList.add('zoomed');
        img.style.transform = `scale(${newScale})`;
    }
}

// =============================================
// POST FORM TOGGLE FUNCTIONALITY
// =============================================

const togglePostFormBtn = document.querySelector('[data-js="toggle-post-form"]');
const postFormContainer = document.getElementById('post-form-container');

if (togglePostFormBtn && postFormContainer) {
    togglePostFormBtn.addEventListener('click', () => {
        const isHidden = postFormContainer.hidden;
        
        if (isHidden) {
            postFormContainer.hidden = false;
            togglePostFormBtn.innerHTML = '<i class="fas fa-times-circle btn-icon"></i> Cancel posting';
            togglePostFormBtn.classList.remove('primary');
            togglePostFormBtn.classList.add('secondary');
            
            // Scroll to form
            setTimeout(() => {
                postFormContainer.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }, 100);
        } else {
            postFormContainer.hidden = true;
            togglePostFormBtn.innerHTML = '<i class="fas fa-plus-circle btn-icon"></i> Post a new item';
            togglePostFormBtn.classList.remove('secondary');
            togglePostFormBtn.classList.add('primary');
        }
    });
}
