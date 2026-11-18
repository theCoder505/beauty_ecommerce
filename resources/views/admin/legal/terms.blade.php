@extends('admin.layouts.app')
@section('title', 'Edit Terms & Conditions')

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
            <p class="text-xs text-gray-500 mt-1">RichTextEditor with headings, lists, links, image resizing, and alignment support.</p>
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
                <x-rich-editor
                    name="content"
                    id="quill-editor"
                    :value="old('content', $content)"
                    placeholder="Enter your store Terms and Conditions, agreement terms, delivery & return rules, warranty, etc..."
                    height="440px"
                />
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
