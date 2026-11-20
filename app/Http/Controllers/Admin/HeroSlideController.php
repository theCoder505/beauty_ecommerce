<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        $slides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create(): View
    {
        return view('admin.hero-slides.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge_text'           => ['nullable', 'string', 'max:255'],
            'title'                => ['required', 'string', 'max:255'],
            'subtitle'             => ['nullable', 'string', 'max:1000'],
            'primary_button_text'  => ['nullable', 'string', 'max:100'],
            'primary_button_url'   => ['nullable', 'string', 'max:255'],
            'secondary_button_text'=> ['nullable', 'string', 'max:100'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'image_file'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:5120'],
            'image_url'            => ['nullable', 'string', 'max:500'],
            'floating_badge_text'  => ['nullable', 'string', 'max:255'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $imagePath = $request->file('image_file')->store('hero-slides', 'public');
        } elseif (!empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        HeroSlide::create([
            'badge_text'           => $validated['badge_text'] ?? null,
            'title'                => $validated['title'],
            'subtitle'             => $validated['subtitle'] ?? null,
            'primary_button_text'  => $validated['primary_button_text'] ?? 'Shop Now',
            'primary_button_url'   => $validated['primary_button_url'] ?? '/shop',
            'secondary_button_text'=> $validated['secondary_button_text'] ?? 'Explore Categories',
            'secondary_button_url' => $validated['secondary_button_url'] ?? '#categories',
            'image'                => $imagePath,
            'floating_badge_text'  => $validated['floating_badge_text'] ?? '100% Original Products',
            'sort_order'           => $validated['sort_order'] ?? 0,
            'is_active'            => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero carousel slide created successfully!');
    }

    public function edit(HeroSlide $heroSlide): View
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function update(Request $request, HeroSlide $heroSlide): RedirectResponse
    {
        $validated = $request->validate([
            'badge_text'           => ['nullable', 'string', 'max:255'],
            'title'                => ['required', 'string', 'max:255'],
            'subtitle'             => ['nullable', 'string', 'max:1000'],
            'primary_button_text'  => ['nullable', 'string', 'max:100'],
            'primary_button_url'   => ['nullable', 'string', 'max:255'],
            'secondary_button_text'=> ['nullable', 'string', 'max:100'],
            'secondary_button_url' => ['nullable', 'string', 'max:255'],
            'image_file'           => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,avif', 'max:5120'],
            'image_url'            => ['nullable', 'string', 'max:500'],
            'floating_badge_text'  => ['nullable', 'string', 'max:255'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
            'is_active'            => ['nullable', 'boolean'],
        ]);

        $data = [
            'badge_text'           => $validated['badge_text'] ?? null,
            'title'                => $validated['title'],
            'subtitle'             => $validated['subtitle'] ?? null,
            'primary_button_text'  => $validated['primary_button_text'] ?? null,
            'primary_button_url'   => $validated['primary_button_url'] ?? null,
            'secondary_button_text'=> $validated['secondary_button_text'] ?? null,
            'secondary_button_url' => $validated['secondary_button_url'] ?? null,
            'floating_badge_text'  => $validated['floating_badge_text'] ?? null,
            'sort_order'           => $validated['sort_order'] ?? 0,
            'is_active'            => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image_file')) {
            // Delete old stored image if it was in hero-slides directory and not default
            if ($heroSlide->image && !str_starts_with($heroSlide->image, 'http') && !str_starts_with($heroSlide->image, 'images/')) {
                Storage::disk('public')->delete($heroSlide->image);
            }
            $data['image'] = $request->file('image_file')->store('hero-slides', 'public');
        } elseif (!empty($validated['image_url'])) {
            $data['image'] = $validated['image_url'];
        }

        $heroSlide->update($data);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero carousel slide updated successfully!');
    }

    public function destroy(HeroSlide $heroSlide): RedirectResponse
    {
        // Enforce the requirement: "min one item has to be there."
        $totalSlides = HeroSlide::count();
        if ($totalSlides <= 1) {
            return redirect()->route('admin.hero-slides.index')
                ->with('error', 'Cannot delete this slide. At least one carousel item must be kept for the hero section.');
        }

        if ($heroSlide->image && !str_starts_with($heroSlide->image, 'http') && !str_starts_with($heroSlide->image, 'images/')) {
            Storage::disk('public')->delete($heroSlide->image);
        }

        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero carousel slide deleted successfully.');
    }

    public function toggle(HeroSlide $heroSlide): RedirectResponse
    {
        $heroSlide->update(['is_active' => !$heroSlide->is_active]);

        return back()->with('success', 'Slide status updated.');
    }
}
