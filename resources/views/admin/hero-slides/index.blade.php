@extends('admin.layouts.app')
@section('title', 'Hero Carousel')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Landing Page Hero Carousel</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300 font-semibold">{{ $slides->count() }} {{ Str::plural('slide', $slides->count()) }}</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1">Manage the hero slides, images, texts, and CTA buttons on the public storefront.</p>
        </div>
        <a href="{{ route('admin.hero-slides.create') }}" class="inline-flex items-center gap-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New Slide
        </a>
    </div>

    {{-- Rules Banner --}}
    <div class="bg-pink-50 dark:bg-pink-950/30 border border-pink-200 dark:border-pink-800/60 rounded-2xl p-4 flex items-start gap-3 text-pink-900 dark:text-pink-200">
        <svg class="w-5 h-5 text-pink-600 dark:text-pink-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-xs sm:text-sm">
            <p class="font-semibold">Carousel Configuration Guidelines</p>
            <p class="mt-0.5 text-pink-800/80 dark:text-pink-300/80">At least one slide must remain in the database at all times. When multiple slides exist, they automatically cycle in an animated carousel on the landing page hero section.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl p-4 text-sm flex items-center justify-between">
        <span>{{ session('success') }}</span>
        <button type="button" onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 rounded-xl p-4 text-sm flex items-center justify-between">
        <span>{{ session('error') }}</span>
        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($slides as $slide)
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm flex flex-col transition hover:shadow-md">
            {{-- Image Preview & Badges --}}
            <div class="relative h-48 bg-gradient-to-tr from-pink-500 via-rose-500 to-fuchsia-600 overflow-hidden">
                <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                {{-- Status pill --}}
                <div class="absolute top-3 right-3 flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.hero-slides.toggle', $slide) }}">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 rounded-full text-xs font-semibold backdrop-blur-md transition {{ $slide->is_active ? 'bg-emerald-500/90 text-white hover:bg-emerald-600' : 'bg-gray-800/90 text-gray-300 hover:bg-gray-700' }}" title="Click to toggle status">
                            {{ $slide->is_active ? '● Active' : '○ Inactive' }}
                        </button>
                    </form>
                </div>

                {{-- Order pill --}}
                <div class="absolute top-3 left-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white/90 text-gray-800 backdrop-blur-md shadow-sm">
                        Order #{{ $slide->sort_order }}
                    </span>
                </div>

                {{-- Title overlay on image --}}
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    @if($slide->badge_text)
                    <p class="text-[11px] font-semibold tracking-wider uppercase text-pink-200 truncate">{{ $slide->badge_text }}</p>
                    @endif
                    <h3 class="font-serif font-bold text-lg leading-tight line-clamp-1">{{ $slide->title }}</h3>
                </div>
            </div>

            {{-- Body Details --}}
            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                    {{ $slide->subtitle ?: 'No subtitle provided.' }}
                </p>

                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 text-xs space-y-1.5">
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Primary Button:</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[140px]">{{ $slide->primary_button_text ?: 'None' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Secondary Button:</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate max-w-[140px]">{{ $slide->secondary_button_text ?: 'None' }}</span>
                    </div>
                    @if($slide->floating_badge_text)
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Floating Badge:</span>
                        <span class="font-medium text-pink-600 dark:text-pink-400 truncate max-w-[140px]">{{ $slide->floating_badge_text }}</span>
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                    <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Edit
                    </a>

                    @if($slides->count() > 1)
                    <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" onsubmit="return confirm('Are you sure you want to delete this hero carousel slide?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-xs font-semibold rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/40 dark:hover:bg-red-900/50 dark:text-red-400 transition" title="Delete Slide">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                    @else
                    <button type="button" disabled title="At least one carousel item must be kept" class="p-2 text-xs rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m4-8H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-4"/></svg>
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
