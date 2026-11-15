@extends('admin.layouts.app')
@section('title', 'Products')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Product Catalog</h1>
            <p class="text-sm text-gray-500 mt-1">Manage perfumes, cosmetics, stock levels, and pricing.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Product
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or SKU..." class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
            </div>
            <div>
                <select name="category_id" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="stock" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    <option value="">All Stock Status</option>
                    <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low Stock (≤10)</option>
                    <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-brand-700 hover:bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">Filter</button>
                @if(request()->anyFilled(['search', 'category_id', 'stock', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-sm transition flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Stock</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Featured</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $p->thumbnail_url }}" alt="{{ $p->name }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-50 shrink-0">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.products.show', $p) }}" class="font-semibold text-gray-900 dark:text-white hover:text-brand-600 transition truncate block max-w-xs">{{ $p->name }}</a>
                                    <span class="text-xs text-gray-400 font-mono">SKU: {{ $p->sku ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            {{ $p->category?->name ?? 'Uncategorized' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($p->is_on_sale)
                                <div class="font-semibold text-brand-600 dark:text-brand-400">${{ number_format($p->sale_price, 2) }}</div>
                                <div class="text-xs text-gray-400 line-through">${{ number_format($p->price, 2) }}</div>
                            @else
                                <div class="font-semibold text-gray-900 dark:text-white">${{ number_format($p->price, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($p->stock <= 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400">Out of Stock</span>
                            @elseif($p->stock <= 10)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Low: {{ $p->stock }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $p->stock }} in stock</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($p->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($p->is_featured)
                                <span class="inline-flex items-center text-amber-500 font-medium text-xs gap-1">★ Featured</span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.products.show', $p) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 rounded-lg transition">View</a>
                            <a href="{{ route('admin.products.edit', $p) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 dark:bg-brand-900/20 dark:hover:bg-brand-900/40 rounded-lg transition">Edit</a>
                            <form action="{{ route('admin.products.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product? All images and reviews will be removed.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/40 rounded-lg transition">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            No products found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="p-4 border-t border-gray-100 dark:border-gray-800">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
