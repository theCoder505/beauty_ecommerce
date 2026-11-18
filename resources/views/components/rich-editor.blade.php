@props([
    'name' => 'content',
    'id' => null,
    'inputId' => null,
    'value' => '',
    'placeholder' => 'Write your content here...',
    'label' => null,
    'height' => '380px',
    'uploadUrl' => route('admin.editor.upload-image'),
    'required' => false,
    'help' => null,
])

@php
    $editorId = $id ?? ('rich-editor-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $name) . '-' . uniqid());
    $targetInputId = $inputId ?? ('input-' . $editorId);
    $initialContent = old($name, $value ?? '');
@endphp

<div class="rich-editor-wrapper space-y-1.5" data-editor-id="{{ $editorId }}">
    @if($label)
    <div class="flex items-center justify-between">
        <label for="{{ $editorId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
        @if($help)
            <span class="text-xs text-gray-400">{{ $help }}</span>
        @endif
    </div>
    @endif

    {{-- Editor outer box --}}
    <div class="rich-editor-box relative rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-xs bg-white dark:bg-gray-900 transition-colors focus-within:ring-2 focus-within:ring-brand-600 focus-within:border-brand-600">
        {{-- Quill Target Element --}}
        <div id="{{ $editorId }}" class="rich-editor-content bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200" style="min-height: {{ $height }};">{!! $initialContent !!}</div>

        {{-- Hidden Form Input --}}
        <input type="hidden" name="{{ $name }}" id="{{ $targetInputId }}" value="{{ $initialContent }}">

        {{-- Uploading overlay indicator --}}
        <div id="{{ $editorId }}-uploading" class="hidden absolute inset-0 bg-white/70 dark:bg-gray-950/70 backdrop-blur-xs flex items-center justify-center z-30 transition-opacity">
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg text-xs font-semibold text-brand-700 dark:text-brand-400">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Uploading image to server...</span>
            </div>
        </div>

        {{-- Image Resizer & Alignment Floating Overlay --}}
        <div id="{{ $editorId }}-img-overlay" class="rich-editor-img-overlay hidden absolute pointer-events-none z-20 border-2 border-brand-600 dark:border-brand-400 rounded-sm">
            {{-- Drag Corner Handles --}}
            <span class="handle handle-nw pointer-events-auto absolute -top-2 -left-2 w-3.5 h-3.5 bg-white border-2 border-brand-600 rounded-full shadow-xs cursor-nwse-resize"></span>
            <span class="handle handle-ne pointer-events-auto absolute -top-2 -right-2 w-3.5 h-3.5 bg-white border-2 border-brand-600 rounded-full shadow-xs cursor-nesw-resize"></span>
            <span class="handle handle-se pointer-events-auto absolute -bottom-2 -right-2 w-3.5 h-3.5 bg-white border-2 border-brand-600 rounded-full shadow-xs cursor-nwse-resize"></span>
            <span class="handle handle-sw pointer-events-auto absolute -bottom-2 -left-2 w-3.5 h-3.5 bg-white border-2 border-brand-600 rounded-full shadow-xs cursor-nesw-resize"></span>

            {{-- Floating Toolbar Above / Below Image --}}
            <div class="img-toolbar pointer-events-auto absolute -top-11 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2 py-1 rounded-xl bg-gray-900/95 dark:bg-gray-800/95 text-white backdrop-blur-md shadow-xl border border-gray-700/60 text-[11px] whitespace-nowrap select-none">
                {{-- Align Left --}}
                <button type="button" data-action="align-left" class="p-1 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition" title="Align Left (Float)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h10M4 18h14"/></svg>
                </button>
                {{-- Align Center --}}
                <button type="button" data-action="align-center" class="p-1 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition" title="Align Center (Block)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M7 12h10M5 18h14"/></svg>
                </button>
                {{-- Align Right --}}
                <button type="button" data-action="align-right" class="p-1 rounded hover:bg-gray-700 text-gray-300 hover:text-white transition" title="Align Right (Float)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M10 12h10M6 18h14"/></svg>
                </button>

                <div class="h-3.5 w-px bg-gray-700 mx-0.5"></div>

                {{-- Quick Width Presets --}}
                <button type="button" data-action="size-25" class="px-1.5 py-0.5 rounded text-[10px] font-semibold hover:bg-gray-700 text-gray-300 hover:text-white">25%</button>
                <button type="button" data-action="size-50" class="px-1.5 py-0.5 rounded text-[10px] font-semibold hover:bg-gray-700 text-gray-300 hover:text-white">50%</button>
                <button type="button" data-action="size-75" class="px-1.5 py-0.5 rounded text-[10px] font-semibold hover:bg-gray-700 text-gray-300 hover:text-white">75%</button>
                <button type="button" data-action="size-100" class="px-1.5 py-0.5 rounded text-[10px] font-semibold hover:bg-gray-700 text-gray-300 hover:text-white">100%</button>

                <div class="h-3.5 w-px bg-gray-700 mx-0.5"></div>

                {{-- Dimension readout --}}
                <span class="dim-badge px-1 text-[10px] font-mono text-gray-400"></span>

                {{-- Delete Image --}}
                <button type="button" data-action="delete" class="p-1 rounded text-red-400 hover:text-red-300 hover:bg-red-950/60 transition" title="Delete Image">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    /* Quill Modern Styling */
    .ql-toolbar.ql-snow {
        border: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        background-color: #f9fafb;
        padding: 0.65rem 0.75rem !important;
        font-family: inherit;
    }
    .dark .ql-toolbar.ql-snow {
        border-bottom-color: #374151 !important;
        background-color: #111827;
    }
    .dark .ql-toolbar .ql-stroke { stroke: #9ca3af !important; }
    .dark .ql-toolbar .ql-fill { fill: #9ca3af !important; }
    .dark .ql-toolbar .ql-picker { color: #9ca3af !important; }
    .dark .ql-toolbar .ql-picker-options {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #e5e7eb !important;
    }
    .ql-container.ql-snow {
        border: none !important;
        font-family: inherit;
        font-size: 0.925rem;
    }
    .dark .ql-container.ql-snow {
        background-color: #111827;
        color: #f3f4f6;
    }
    .ql-editor {
        line-height: 1.8;
        padding: 1.25rem 1.5rem !important;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
        left: 1.5rem !important;
    }

    /* Images inside editor */
    .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: outline 0.15s ease;
    }
    .ql-editor img:hover {
        outline: 2px solid #16a34a;
    }

    /* Alignment classes in Quill editor */
    .ql-editor .ql-align-center,
    .ql-editor p.ql-align-center,
    .ql-editor h1.ql-align-center,
    .ql-editor h2.ql-align-center,
    .ql-editor h3.ql-align-center {
        text-align: center;
    }
    .ql-editor .ql-align-right,
    .ql-editor p.ql-align-right,
    .ql-editor h1.ql-align-right,
    .ql-editor h2.ql-align-right,
    .ql-editor h3.ql-align-right {
        text-align: right;
    }
    .ql-editor .ql-align-left,
    .ql-editor p.ql-align-left {
        text-align: left;
    }
    .ql-editor .ql-align-justify,
    .ql-editor p.ql-align-justify {
        text-align: justify;
    }

    /* Image alignments in Quill editor */
    .ql-editor img.ql-align-center,
    .ql-editor img.editor-img-center,
    .ql-editor p.ql-align-center img {
        display: block;
        margin-left: auto;
        margin-right: auto;
        float: none;
    }
    .ql-editor img.ql-align-left,
    .ql-editor img.editor-img-left,
    .ql-editor p.ql-align-left img {
        float: left;
        margin-right: 1.25rem;
        margin-bottom: 0.75rem;
        margin-top: 0.25rem;
        display: inline-block;
    }
    .ql-editor img.ql-align-right,
    .ql-editor img.editor-img-right,
    .ql-editor p.ql-align-right img {
        float: right;
        margin-left: 1.25rem;
        margin-bottom: 0.75rem;
        margin-top: 0.25rem;
        display: inline-block;
    }
    .ql-editor u {
        text-decoration: underline !important;
    }

    /* Overlay styles */
    .rich-editor-img-overlay {
        box-sizing: border-box;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
(function() {
    // 1. Extend Image Blot in Quill to preserve style, class, width, height, and alt attributes
    if (window.Quill) {
        setupQuillImageBlot();
    } else {
        document.addEventListener('DOMContentLoaded', setupQuillImageBlot);
    }

    function setupQuillImageBlot() {
        if (!window.Quill || window.__QUILL_IMAGE_BLOT_SETUP__) return;
        window.__QUILL_IMAGE_BLOT_SETUP__ = true;

        try {
            const BaseImage = Quill.import('formats/image');
            class ExtendedImage extends BaseImage {
                static create(value) {
                    const src = (typeof value === 'object' && value !== null) ? value.src : value;
                    const node = super.create(src);
                    if (typeof value === 'object' && value !== null) {
                        if (value.style) node.setAttribute('style', value.style);
                        if (value.class) node.setAttribute('class', value.class);
                        if (value.width) node.setAttribute('width', value.width);
                        if (value.height) node.setAttribute('height', value.height);
                        if (value.alt) node.setAttribute('alt', value.alt);
                    }
                    return node;
                }
                static formats(domNode) {
                    const formats = {};
                    if (domNode.hasAttribute('src')) formats.src = domNode.getAttribute('src');
                    if (domNode.hasAttribute('style')) formats.style = domNode.getAttribute('style');
                    if (domNode.hasAttribute('class')) formats.class = domNode.getAttribute('class');
                    if (domNode.hasAttribute('width')) formats.width = domNode.getAttribute('width');
                    if (domNode.hasAttribute('height')) formats.height = domNode.getAttribute('height');
                    if (domNode.hasAttribute('alt')) formats.alt = domNode.getAttribute('alt');
                    return formats;
                }
                format(name, value) {
                    if (['style', 'class', 'width', 'height', 'alt'].includes(name)) {
                        if (value) {
                            this.domNode.setAttribute(name, value);
                        } else {
                            this.domNode.removeAttribute(name);
                        }
                    } else {
                        super.format(name, value);
                    }
                }
            }
            ExtendedImage.blotName = 'image';
            ExtendedImage.tagName = 'IMG';
            Quill.register(ExtendedImage, true);
        } catch (e) {
            console.warn('ExtendedImage registration warning:', e);
        }
    }

    // 2. Main RichEditor Initializer
    window.initBeautyRichEditor = function(config) {
        const { editorId, inputId, uploadUrl, placeholder } = config;
        const editorElem = document.getElementById(editorId);
        const inputElem = document.getElementById(inputId);
        const uploadingElem = document.getElementById(editorId + '-uploading');
        const overlayElem = document.getElementById(editorId + '-img-overlay');
        const editorBox = editorElem ? editorElem.closest('.rich-editor-box') : null;

        if (!editorElem || !inputElem || !editorBox) return;

        // Ensure Image Blot is set
        setupQuillImageBlot();

        const toolbarOptions = [
            [{ 'header': [1, 2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': '' }, { 'align': 'center' }, { 'align': 'right' }, { 'align': 'justify' }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
        ];

        const quill = new Quill('#' + editorId, {
            theme: 'snow',
            placeholder: placeholder || 'Write your content...',
            modules: {
                toolbar: toolbarOptions
            }
        });

        // Sync helper
        function syncToInput() {
            const html = quill.root.innerHTML;
            inputElem.value = (html === '<p><br></p>' || html.trim() === '') ? '' : html;
        }

        quill.on('text-change', syncToInput);

        // Form submit safety: grab latest innerHTML
        const form = inputElem.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                syncToInput();
            });
        }

        // Custom Image Upload Handler
        const toolbar = quill.getModule('toolbar');
        if (toolbar) {
            toolbar.addHandler('image', function() {
                selectAndUploadImage();
            });
        }

        function selectAndUploadImage() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*';
            fileInput.click();

            fileInput.onchange = function() {
                const file = fileInput.files[0];
                if (file) {
                    uploadImageFile(file);
                }
            };
        }

        async function uploadImageFile(file) {
            if (uploadingElem) uploadingElem.classList.remove('hidden');

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            try {
                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data && data.url) {
                        insertImageIntoEditor(data.url);
                        return;
                    }
                }
                fallbackBase64(file);
            } catch (err) {
                fallbackBase64(file);
            } finally {
                if (uploadingElem) uploadingElem.classList.add('hidden');
            }
        }

        function fallbackBase64(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                insertImageIntoEditor(e.target.result);
            };
            reader.readAsDataURL(file);
        }

        function insertImageIntoEditor(url) {
            const range = quill.getSelection(true) || { index: quill.getLength() };
            quill.insertEmbed(range.index, 'image', url);
            quill.setSelection(range.index + 1);
            syncToInput();
        }

        // Drag & Drop image files
        quill.root.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        quill.root.addEventListener('drop', function(e) {
            e.preventDefault();
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                if (file.type.startsWith('image/')) {
                    uploadImageFile(file);
                }
            }
        });

        // Paste image handler
        quill.root.addEventListener('paste', function(e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (!clipboardData) return;
            const items = clipboardData.items;
            if (!items) return;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const file = items[i].getAsFile();
                    if (file) {
                        e.preventDefault();
                        uploadImageFile(file);
                        break;
                    }
                }
            }
        });

        // ==========================================
        // 3. Interactive Image Resize & Alignment System
        // ==========================================
        let selectedImg = null;
        let isResizing = false;
        let startX = 0;
        let startWidth = 0;
        let aspectRatio = 1;
        let currentHandle = null;

        function updateOverlayPosition() {
            if (!selectedImg || !overlayElem || !editorBox) return;

            const imgRect = selectedImg.getBoundingClientRect();
            const boxRect = editorBox.getBoundingClientRect();

            overlayElem.style.top = (imgRect.top - boxRect.top) + 'px';
            overlayElem.style.left = (imgRect.left - boxRect.left) + 'px';
            overlayElem.style.width = imgRect.width + 'px';
            overlayElem.style.height = imgRect.height + 'px';

            const badge = overlayElem.querySelector('.dim-badge');
            if (badge) {
                badge.textContent = Math.round(imgRect.width) + '×' + Math.round(imgRect.height);
            }
        }

        function showOverlay(img) {
            selectedImg = img;
            overlayElem.classList.remove('hidden');
            updateOverlayPosition();
        }

        function hideOverlay() {
            if (isResizing) return;
            selectedImg = null;
            if (overlayElem) overlayElem.classList.add('hidden');
        }

        // Image click inside editor
        quill.root.addEventListener('click', function(e) {
            if (e.target && e.target.tagName === 'IMG') {
                e.stopPropagation();
                showOverlay(e.target);
            } else {
                hideOverlay();
            }
        });

        // Hide overlay when clicking outside
        document.addEventListener('click', function(e) {
            if (!editorBox.contains(e.target)) {
                hideOverlay();
            }
        });

        // Update overlay on scroll & resize
        quill.root.addEventListener('scroll', function() {
            if (selectedImg) updateOverlayPosition();
        });
        window.addEventListener('resize', function() {
            if (selectedImg) updateOverlayPosition();
        });

        // Resize handles drag
        const handles = overlayElem.querySelectorAll('.handle');
        handles.forEach(function(handle) {
            handle.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!selectedImg) return;

                isResizing = true;
                currentHandle = handle;
                startX = e.clientX;
                startWidth = selectedImg.offsetWidth;
                const startHeight = selectedImg.offsetHeight;
                aspectRatio = startWidth / (startHeight || 1);

                document.addEventListener('mousemove', onHandleMouseMove);
                document.addEventListener('mouseup', onHandleMouseUp);
            });
        });

        function onHandleMouseMove(e) {
            if (!isResizing || !selectedImg) return;

            const isLeft = currentHandle.classList.contains('handle-nw') || currentHandle.classList.contains('handle-sw');
            const deltaX = e.clientX - startX;
            let newWidth = isLeft ? (startWidth - deltaX) : (startWidth + deltaX);

            const maxWidth = editorBox.clientWidth - 40;
            newWidth = Math.max(50, Math.min(newWidth, maxWidth));

            selectedImg.style.width = Math.round(newWidth) + 'px';
            selectedImg.style.height = 'auto';
            selectedImg.setAttribute('width', Math.round(newWidth));

            updateOverlayPosition();
        }

        function onHandleMouseUp() {
            if (!isResizing) return;
            isResizing = false;
            currentHandle = null;

            document.removeEventListener('mousemove', onHandleMouseMove);
            document.removeEventListener('mouseup', onHandleMouseUp);

            updateOverlayPosition();
            syncToInput();
        }

        // Toolbar Buttons on Image Overlay
        const toolbarBtns = overlayElem.querySelectorAll('.img-toolbar button');
        toolbarBtns.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!selectedImg) return;

                const action = btn.getAttribute('data-action');

                if (action === 'align-center') {
                    // Center alignment
                    selectedImg.style.display = 'block';
                    selectedImg.style.marginLeft = 'auto';
                    selectedImg.style.marginRight = 'auto';
                    selectedImg.style.float = 'none';
                    selectedImg.classList.remove('editor-img-left', 'editor-img-right', 'ql-align-left', 'ql-align-right');
                    selectedImg.classList.add('editor-img-center', 'ql-align-center');

                    if (selectedImg.parentElement && selectedImg.parentElement.tagName === 'P') {
                        selectedImg.parentElement.classList.add('ql-align-center');
                        selectedImg.parentElement.classList.remove('ql-align-left', 'ql-align-right');
                    }
                } else if (action === 'align-left') {
                    // Left float alignment
                    selectedImg.style.display = 'inline-block';
                    selectedImg.style.float = 'left';
                    selectedImg.style.marginLeft = '0';
                    selectedImg.style.marginRight = '1.5rem';
                    selectedImg.style.marginBottom = '1rem';
                    selectedImg.style.marginTop = '0.5rem';
                    selectedImg.classList.remove('editor-img-center', 'editor-img-right', 'ql-align-center', 'ql-align-right');
                    selectedImg.classList.add('editor-img-left', 'ql-align-left');

                    if (selectedImg.parentElement && selectedImg.parentElement.tagName === 'P') {
                        selectedImg.parentElement.classList.remove('ql-align-center', 'ql-align-right');
                    }
                } else if (action === 'align-right') {
                    // Right float alignment
                    selectedImg.style.display = 'inline-block';
                    selectedImg.style.float = 'right';
                    selectedImg.style.marginLeft = '1.5rem';
                    selectedImg.style.marginRight = '0';
                    selectedImg.style.marginBottom = '1rem';
                    selectedImg.style.marginTop = '0.5rem';
                    selectedImg.classList.remove('editor-img-center', 'editor-img-left', 'ql-align-center', 'ql-align-left');
                    selectedImg.classList.add('editor-img-right', 'ql-align-right');

                    if (selectedImg.parentElement && selectedImg.parentElement.tagName === 'P') {
                        selectedImg.parentElement.classList.remove('ql-align-center', 'ql-align-left');
                    }
                } else if (action === 'size-25') {
                    selectedImg.style.width = '25%';
                    selectedImg.style.height = 'auto';
                } else if (action === 'size-50') {
                    selectedImg.style.width = '50%';
                    selectedImg.style.height = 'auto';
                } else if (action === 'size-75') {
                    selectedImg.style.width = '75%';
                    selectedImg.style.height = 'auto';
                } else if (action === 'size-100') {
                    selectedImg.style.width = '100%';
                    selectedImg.style.height = 'auto';
                    selectedImg.style.display = 'block';
                    selectedImg.style.marginLeft = 'auto';
                    selectedImg.style.marginRight = 'auto';
                    selectedImg.style.float = 'none';
                } else if (action === 'delete') {
                    selectedImg.remove();
                    hideOverlay();
                    syncToInput();
                    return;
                }

                updateOverlayPosition();
                syncToInput();
            });
        });

        // Keyboard delete/backspace removes selected image
        document.addEventListener('keydown', function(e) {
            if ((e.key === 'Delete' || e.key === 'Backspace') && selectedImg && !isResizing) {
                if (document.activeElement !== quill.root) {
                    selectedImg.remove();
                    hideOverlay();
                    syncToInput();
                }
            } else if (e.key === 'Escape' && selectedImg) {
                hideOverlay();
            }
        });
    };
})();
</script>
@endpush
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.initBeautyRichEditor === 'function') {
        window.initBeautyRichEditor({
            editorId: @json($editorId),
            inputId: @json($targetInputId),
            uploadUrl: @json($uploadUrl),
            placeholder: @json($placeholder)
        });
    }
});
</script>
@endpush
