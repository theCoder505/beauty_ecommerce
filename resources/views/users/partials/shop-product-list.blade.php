@if($products->isEmpty())
<div class="text-center py-24" id="shop-empty-state">
    <svg class="w-16 h-16 text-gray-200 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
    <p class="text-gray-500 font-semibold">No products found</p>
    <button type="button" onclick="resetShopFilters()" class="mt-4 inline-block text-brand-700 dark:text-brand-400 hover:underline text-sm font-semibold">Clear all filters</button>
</div>
@else
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5" id="shop-products-grid">
    @foreach($products as $product)
    @include('users.partials.product-card', ['product' => $product, 'currency' => $currency])
    @endforeach
</div>
@endif
