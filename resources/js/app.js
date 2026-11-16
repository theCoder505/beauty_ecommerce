import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Expose SweetAlert2 globally
window.Swal = Swal;

// Global App Timezone & Formatting Helper
window.getAppTimezone = function() {
    return window.APP_TIMEZONE || document.querySelector('meta[name="app-timezone"]')?.getAttribute('content') || 'UTC';
};

window.formatAppDateTime = function(dateInput, options = {}) {
    if (!dateInput) return '';
    const date = (dateInput instanceof Date) ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat(undefined, {
        timeZone: window.getAppTimezone(),
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        ...options
    }).format(date);
};

window.formatAppDate = function(dateInput, options = {}) {
    if (!dateInput) return '';
    const date = (dateInput instanceof Date) ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat(undefined, {
        timeZone: window.getAppTimezone(),
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        ...options
    }).format(date);
};

window.formatAppTime = function(dateInput, options = {}) {
    if (!dateInput) return '';
    const date = (dateInput instanceof Date) ? dateInput : new Date(dateInput);
    if (isNaN(date.getTime())) return '';
    return new Intl.DateTimeFormat(undefined, {
        timeZone: window.getAppTimezone(),
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
        ...options
    }).format(date);
};

window.renderAppTimes = function() {
    document.querySelectorAll('[data-app-time]').forEach(el => {
        const raw = el.getAttribute('data-app-time');
        if (!raw) return;
        const formatType = el.getAttribute('data-format') || 'datetime';
        if (formatType === 'date') {
            el.textContent = window.formatAppDate(raw);
        } else if (formatType === 'time') {
            el.textContent = window.formatAppTime(raw);
        } else {
            el.textContent = window.formatAppDateTime(raw);
        }
    });
};

// Global Toast helper
window.toast = function(title, icon = 'success') {
    const isDark = document.documentElement.classList.contains('dark');
    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        background: isDark ? '#1f2937' : '#ffffff',
        color: isDark ? '#f3f4f6' : '#111827',
        didOpen: (t) => {
            t.addEventListener('mouseenter', Swal.stopTimer);
            t.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
};

// Global AJAX Add to Cart helper (No page reload)
window.addToCartAjax = async function(productId, quantity = 1, buttonEl = null) {
    if (!productId) return;

    let originalHtml = '';
    if (buttonEl) {
        if (buttonEl.disabled) return;
        buttonEl.disabled = true;
        originalHtml = buttonEl.innerHTML;
        buttonEl.innerHTML = `
            <svg class="animate-spin h-3.5 w-3.5 mx-auto text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="sr-only">Adding…</span>
        `;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Update cart badge in header
            const badge = document.getElementById('header-cart-count');
            if (badge) {
                badge.textContent = data.count;
                if (data.count > 0) {
                    badge.classList.remove('hidden');
                }
            }

            if (window.toast) {
                window.toast(data.message || 'Added to shopping bag!', 'success');
            }
        } else {
            const msg = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Failed to add item to cart.');
            if (window.toast) {
                window.toast(msg, 'error');
            }
        }
    } catch (err) {
        console.error('AJAX add to cart failed:', err);
        if (window.toast) {
            window.toast('Network error. Please try again.', 'error');
        }
    } finally {
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalHtml;
        }
    }
};

// Global AJAX Wishlist Toggle helper (No page reload)
window.toggleWishlist = async function(productId, buttonEl = null) {
    if (!productId) return;

    if (buttonEl && buttonEl.dataset.loading === 'true') return;
    if (buttonEl) buttonEl.dataset.loading = 'true';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/wishlist/toggle/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: productId })
        });

        if (response.status === 419) {
            if (window.toast) {
                window.toast('Session expired. Please refresh the page.', 'warning');
            }
            return;
        }

        if (response.status === 401 || (response.redirected && response.url.includes('/login'))) {
            // Unauthenticated user: prompt politely with Swal
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'info',
                    title: 'Sign In Required',
                    text: 'Please sign in to save your favorite beauty products to your wishlist.',
                    showCancelButton: true,
                    confirmButtonText: 'Sign In',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#15803d',
                    cancelButtonColor: '#6b7280',
                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                    color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                    customClass: {
                        popup: 'rounded-2xl border border-cream-200 dark:border-gray-700 shadow-2xl',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm',
                        cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/login';
                    }
                });
            } else {
                window.location.href = '/login';
            }
            return;
        }

        const data = await response.json();

        if (response.ok && typeof data.wishlisted !== 'undefined') {
            // Update button visual state
            if (buttonEl) {
                const icon = buttonEl.querySelector('svg');
                if (data.wishlisted) {
                    buttonEl.classList.remove('text-gray-400');
                    buttonEl.classList.add('text-red-500', 'opacity-100');
                    buttonEl.setAttribute('title', 'Remove from wishlist');
                    if (icon) {
                        icon.classList.add('fill-current');
                    }
                } else {
                    buttonEl.classList.remove('text-red-500');
                    buttonEl.classList.add('text-gray-400');
                    buttonEl.setAttribute('title', 'Add to wishlist');
                    if (icon) {
                        icon.classList.remove('fill-current');
                    }
                }
            }

            // Synchronize any matching wishlist buttons for this product on the current page
            document.querySelectorAll(`button[onclick*="toggleWishlist(${productId}"]`).forEach(btn => {
                if (btn !== buttonEl) {
                    const icon = btn.querySelector('svg');
                    if (data.wishlisted) {
                        btn.classList.remove('text-gray-400');
                        btn.classList.add('text-red-500', 'opacity-100');
                        btn.setAttribute('title', 'Remove from wishlist');
                        if (icon) icon.classList.add('fill-current');
                    } else {
                        btn.classList.remove('text-red-500');
                        btn.classList.add('text-gray-400');
                        btn.setAttribute('title', 'Add to wishlist');
                        if (icon) icon.classList.remove('fill-current');
                    }
                }
            });

            // Update header wishlist badge count
            const badge = document.getElementById('header-wishlist-count');
            if (badge) {
                badge.textContent = data.count;
                if (data.count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // If removed from wishlist on the wishlist page, animate and remove the card nicely
            if (!data.wishlisted) {
                const targetCards = document.querySelectorAll(`[data-product-id="${productId}"], #product-card-${productId}`);
                targetCards.forEach(card => {
                    const grid = card.closest('#wishlist-grid') || (window.location.pathname.includes('/wishlist') ? card.parentElement : null);
                    if (grid) {
                        // Smooth removal animation: scale down + fade + slide up
                        card.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                        card.style.transform = 'scale(0.85) translateY(-12px)';
                        card.style.opacity = '0';
                        card.style.pointerEvents = 'none';

                        setTimeout(() => {
                            card.remove();

                            // Update item counter on wishlist page
                            const countEl = document.getElementById('wishlist-count-number');
                            if (countEl) countEl.textContent = data.count;

                            const remainingCards = grid.querySelectorAll('.product-card');
                            if (remainingCards.length === 0 || data.count === 0) {
                                grid.classList.add('hidden');
                                const emptyState = document.getElementById('wishlist-empty-state');
                                const counterHeader = document.getElementById('wishlist-items-counter');
                                if (counterHeader) counterHeader.classList.add('hidden');
                                if (emptyState) {
                                    emptyState.classList.remove('hidden');
                                    emptyState.style.opacity = '0';
                                    emptyState.style.transform = 'translateY(16px)';
                                    emptyState.style.transition = 'all 0.45s ease-out';
                                    requestAnimationFrame(() => {
                                        emptyState.style.opacity = '1';
                                        emptyState.style.transform = 'translateY(0)';
                                    });
                                }
                            }
                        }, 400);
                    }
                });
            }

            if (window.toast) {
                window.toast(
                    data.wishlisted ? 'Saved to your wishlist!' : 'Removed from wishlist',
                    data.wishlisted ? 'success' : 'info'
                );
            }
        }
    } catch (err) {
        console.error('AJAX wishlist toggle failed:', err);
        if (window.toast) {
            window.toast('Could not update wishlist.', 'error');
        }
    } finally {
        if (buttonEl) buttonEl.dataset.loading = 'false';
    }
};

// Global alert override (Point 2: "Add Swal alert in all kind of alerts, no js alert() simply at all please")
window.alert = function (message) {
    const isDark = document.documentElement.classList.contains('dark');
    return Swal.fire({
        text: message,
        icon: 'info',
        confirmButtonColor: '#15803d',
        background: isDark ? '#111827' : '#ffffff',
        color: isDark ? '#f3f4f6' : '#111827',
        customClass: {
            popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
        }
    });
};

// Dark/light theme initialization (Point 4)
(function() {
    const html = document.documentElement;
    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (stored === 'dark' || (!stored && prefersDark)) {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
})();

window.toggleTheme = function () {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: isDark ? 'light' : 'dark' } }));
};

// Universal Image Preview handler (Point 9)
window.setupImagePreviews = function() {
    document.querySelectorAll('input[type="file"]').forEach(input => {
        if (input.dataset.previewBound) return;
        input.dataset.previewBound = 'true';

        input.addEventListener('change', function(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;

            // Target explicitly specified by data-preview attribute
            const targetSelector = input.getAttribute('data-preview');
            if (targetSelector) {
                const targetImg = document.querySelector(targetSelector);
                if (targetImg && files[0].type.startsWith('image/')) {
                    targetImg.src = URL.createObjectURL(files[0]);
                    targetImg.classList.remove('hidden');
                    return;
                }
            }

            // Container explicitly specified by data-preview-container attribute
            const containerSelector = input.getAttribute('data-preview-container');
            let previewContainer = containerSelector ? document.querySelector(containerSelector) : null;

            // Single image input without target: check ONLY immediate parent container (never ancestor form)
            if (!containerSelector && !input.multiple) {
                const parent = input.parentElement;
                const existingImg = parent ? parent.querySelector('.preview-target, img[id*="preview"]') : null;
                if (existingImg && files.length === 1 && files[0].type.startsWith('image/')) {
                    existingImg.src = URL.createObjectURL(files[0]);
                    existingImg.classList.remove('hidden');
                    return;
                }
            }

            // Dynamic preview container for multiple or inputs without target
            if (!previewContainer) {
                previewContainer = input.parentElement.querySelector('.image-preview-container');
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.className = 'image-preview-container flex flex-wrap gap-3 mt-3';
                    input.parentElement.appendChild(previewContainer);
                }
            }
            previewContainer.innerHTML = '';

            files.forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'relative group w-20 h-20 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-sm';
                
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'w-full h-full object-cover';
                
                const badge = document.createElement('span');
                badge.className = 'absolute bottom-0 inset-x-0 bg-black/60 text-white text-[9px] text-center truncate px-1 py-0.5';
                badge.textContent = file.name;

                wrapper.appendChild(img);
                wrapper.appendChild(badge);
                previewContainer.appendChild(wrapper);
            });
        });
    });
};

// Form confirmation interception (Point 2)
document.addEventListener('DOMContentLoaded', () => {
    // Convert any onsubmit="return confirm(...)" to data-confirm
    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
        const onsubmitAttr = form.getAttribute('onsubmit');
        const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
        if (match) {
            form.removeAttribute('onsubmit');
            form.setAttribute('data-confirm', match[1]);
        }
    });

    // Form submit listener for data-confirm
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.getAttribute) return;
        const confirmMsg = form.getAttribute('data-confirm') || (form.dataset && form.dataset.confirm);
        if (confirmMsg && !form.dataset.confirmed) {
            e.preventDefault();
            const isDark = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: 'Are you sure?',
                text: confirmMsg,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, proceed',
                cancelButtonText: 'Cancel',
                background: isDark ? '#111827' : '#ffffff',
                color: isDark ? '#f3f4f6' : '#111827',
                customClass: {
                    popup: 'rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    });

    // Initialize image previews
    window.setupImagePreviews();

    // Render any data-app-time elements
    window.renderAppTimes();

    // Auto-dismiss classic toasts if rendered
    document.querySelectorAll('.toast').forEach(el => {
        setTimeout(() => el.remove(), 4200);
    });
});
