import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import Swal from 'sweetalert2';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

// Expose SweetAlert2 globally
window.Swal = Swal;

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

    // Auto-dismiss classic toasts if rendered
    document.querySelectorAll('.toast').forEach(el => {
        setTimeout(() => el.remove(), 4200);
    });
});
