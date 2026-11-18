@extends('admin.layouts.app')
@section('title', 'Edit Terms & Conditions')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
<style>
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-color: #e5e7eb;
        background-color: #f9fafb;
    }
    .dark .ql-toolbar.ql-snow {
        border-color: #374151;
        background-color: #1f2937;
    }
    .dark .ql-toolbar .ql-stroke {
        stroke: #9ca3af;
    }
    .dark .ql-toolbar .ql-fill {
        fill: #9ca3af;
    }
    .dark .ql-toolbar .ql-picker {
        color: #9ca3af;
    }
    .dark .ql-toolbar .ql-picker-options {
        background-color: #1f2937;
        border-color: #374151;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        border-color: #e5e7eb;
        font-family: inherit;
        font-size: 0.875rem;
    }
    .dark .ql-container.ql-snow {
        border-color: #374151;
        background-color: #111827;
        color: #f3f4f6;
    }
    .ql-editor {
        min-height: 420px;
        line-height: 1.7;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
    }
    .ql-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        margin: 1rem 0;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-500 mb-1">
                <a href="{{ route('admin.settings.index') }}" class="hover:underline">Settings</a>
                <span>/</span>
                <span>Terms & Conditions</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Terms & Conditions Editor</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">RichTextEditor with headings, lists, links, and inline image support.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('terms') }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>View Public Page ↗</span>
            </a>
            <a href="{{ route('admin.privacy.edit') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition">
                <span>Edit Privacy Policy &rarr;</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl p-4 text-xs font-semibold flex items-center gap-2 shadow-xs">
        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <form action="{{ route('admin.terms.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-4 shadow-sm">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Document Content</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Format legal clauses, insert headers, highlight clauses, and embed policy visuals or badges.</p>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-mono text-gray-400">Public: {{ url('/terms-and-conditions') }}</span>
                </div>
            </div>

            <div>
                <div id="quill-editor" class="bg-white dark:bg-gray-900">{!! old('content', $content) !!}</div>
                <input type="hidden" name="content" id="hidden-content" value="{{ old('content', $content) }}">
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('admin.settings.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                &larr; Back to Settings
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold shadow-sm transition">
                Save Terms & Conditions
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Enter your store Terms and Conditions, agreement terms, delivery & return rules, warranty, etc...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link', 'image'],
                ['clean']
            ]
        }
    });

    // Custom Image Handler uploading to /admin/editor/upload-image
    const toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', function() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = async function() {
            const file = input.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('{{ route('admin.editor.upload-image') }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    if (data.url) {
                        const range = quill.getSelection(true) || { index: quill.getLength() };
                        quill.insertEmbed(range.index, 'image', data.url);
                        quill.setSelection(range.index + 1);
                        return;
                    }
                }
                fallbackBase64(file);
            } catch (err) {
                fallbackBase64(file);
            }
        };

        function fallbackBase64(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const range = quill.getSelection(true) || { index: quill.getLength() };
                quill.insertEmbed(range.index, 'image', e.target.result);
                quill.setSelection(range.index + 1);
            };
            reader.readAsDataURL(file);
        }
    });

    const hiddenInput = document.getElementById('hidden-content');
    
    quill.on('text-change', function() {
        hiddenInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
    });

    const form = hiddenInput.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            hiddenInput.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });
    }
});
</script>
@endpush
