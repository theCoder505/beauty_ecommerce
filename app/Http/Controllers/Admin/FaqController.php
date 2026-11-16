<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(Request $request): View
    {
        $query = Faq::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->orderBy('sort_order')->orderByDesc('id')->get();
        $categories = Faq::select('category')->distinct()->pluck('category')->filter()->values();

        return view('admin.faqs.index', compact('faqs', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question'   => ['required', 'string', 'max:255'],
            'answer'     => ['required', 'string'],
            'category'   => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Faq::create($data);

        return back()->with('success', 'FAQ created successfully.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $data = $request->validate([
            'question'   => ['required', 'string', 'max:255'],
            'answer'     => ['required', 'string'],
            'category'   => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $faq->update($data);

        return back()->with('success', 'FAQ updated successfully.');
    }

    public function toggle(Request $request, Faq $faq): JsonResponse|RedirectResponse
    {
        $faq->update(['is_active' => !$faq->is_active]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'is_active' => $faq->is_active,
                'message'   => 'FAQ status updated.',
            ]);
        }

        return back()->with('success', 'FAQ status updated.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        return back()->with('success', 'FAQ deleted successfully.');
    }
}
