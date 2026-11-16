<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        $query = PromoCode::query()->latest();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $promoCodes = $query->paginate(15)->withQueryString();

        return view('admin.promo-codes.index', compact('promoCodes'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price', 'sale_price', 'sku']);
        return view('admin.promo-codes.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'type'         => ['required', 'in:percentage,fixed'],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'apply_to'     => ['required', 'in:all,specific'],
            'product_ids'  => ['nullable', 'array'],
            'product_ids.*'=> ['integer', 'exists:products,id'],
            'min_spend'    => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'  => ['nullable', 'integer', 'min:1'],
            'is_active'    => ['nullable', 'boolean'],
            'starts_at'    => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        if ($validated['apply_to'] === 'specific' && empty($validated['product_ids'])) {
            return back()->withErrors(['product_ids' => 'Please select at least one product when applying to specific products.'])->withInput();
        }

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);
        if ($validated['apply_to'] === 'all') {
            $validated['product_ids'] = null;
        }

        PromoCode::create($validated);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code created successfully.');
    }

    public function edit(PromoCode $promo_code): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price', 'sale_price', 'sku']);
        return view('admin.promo-codes.edit', [
            'promoCode' => $promo_code,
            'products'  => $products,
        ]);
    }

    public function update(Request $request, PromoCode $promo_code): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:50', 'unique:promo_codes,code,' . $promo_code->id],
            'type'         => ['required', 'in:percentage,fixed'],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'apply_to'     => ['required', 'in:all,specific'],
            'product_ids'  => ['nullable', 'array'],
            'product_ids.*'=> ['integer', 'exists:products,id'],
            'min_spend'    => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'  => ['nullable', 'integer', 'min:1'],
            'is_active'    => ['nullable', 'boolean'],
            'starts_at'    => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['type'] === 'percentage' && $validated['value'] > 100) {
            return back()->withErrors(['value' => 'Percentage discount cannot exceed 100%.'])->withInput();
        }

        if ($validated['apply_to'] === 'specific' && empty($validated['product_ids'])) {
            return back()->withErrors(['product_ids' => 'Please select at least one product when applying to specific products.'])->withInput();
        }

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');
        if ($validated['apply_to'] === 'all') {
            $validated['product_ids'] = null;
        }

        $promo_code->update($validated);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code updated successfully.');
    }

    public function destroy(PromoCode $promo_code): RedirectResponse
    {
        $promo_code->delete();
        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code deleted successfully.');
    }

    public function toggle(PromoCode $promo_code): RedirectResponse
    {
        $promo_code->update(['is_active' => !$promo_code->is_active]);
        $status = $promo_code->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Promo code \"{$promo_code->code}\" has been {$status}.");
    }
}
