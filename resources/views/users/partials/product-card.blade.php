@php $currency = app(\App\Services\SettingService::class)->get('currency_symbol', '৳'); @endphp
<div class="product-card group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-cream-200 dark:border-gray-700 transition duration-300">
    {{-- Image --}}
    <div class="relative overflow-hidden aspect-square bg-cream-100 dark:bg-gray-700">
        <a href="{{ route('products.show', $product->slug) }}">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
        </a>
        {{-- Sale badge --}}
        @if($product->is_on_sale)
        <span class="absolute top-3 left-3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-lg">
            -{{ $product->discount_percent }}%
        </span>
        @endif
        {{-- Featured badge --}}
        @if($product->is_featured)
        <span class="absolute top-3 right-3 bg-brand-700 text-white text-[10px] font-bold px-2 py-1 rounded-lg">
            Featured
        </span>
        @endif
        {{-- Wishlist button --}}
        @auth
        <button
            x-data
            @click.prevent="
                fetch('{{ route('wishlist.toggle', $product->id) }}', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json'}
                }).then(r => r.json()).then(d => {
                    $el.classList.toggle('text-red-500', d.wishlisted);
                    $el.classList.toggle('text-gray-400', !d.wishlisted);
                })
            "
            class="absolute bottom-3 right-3 bg-white dark:bg-gray-800 p-2 rounded-full shadow text-gray-400 hover:text-red-500 transition opacity-0 group-hover:opacity-100"
            aria-label="Wishlist">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </button>
        @endauth
    </div>

    {{-- Info --}}
    <div class="p-4">
        <p class="text-xs text-gray-400 mb-1">{{ $product->category->name ?? '' }}</p>
        <h3 class="font-semibold text-sm text-gray-900 dark:text-gray-100 leading-tight line-clamp-2 mb-2">
            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-brand-700 dark:hover:text-brand-400 transition">{{ $product->name }}</a>
        </h3>

        {{-- Description for List View --}}
        @if(!empty($product->description))
        <p class="product-list-desc hidden text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2 leading-relaxed">
            {{ Str::limit(strip_tags($product->description), 160) }}
        </p>
        @endif

        {{-- Rating --}}
        @php $avg = $product->approved_reviews_avg_rating ?? $product->average_rating ?? 0; $count = $product->approved_reviews_count ?? 0; @endphp
        @if($count > 0)
        <div class="flex items-center gap-1 mb-2">
            @for($i = 1; $i <= 5; $i++)
            <svg class="w-3 h-3 {{ $i <= round($avg) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600' }} fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
            <span class="text-[11px] text-gray-400">({{ $count }})</span>
        </div>
        @endif

        {{-- Price + Cart --}}
        <div class="flex items-center justify-between mt-auto">
            <div>
                <span class="font-bold text-brand-700 dark:text-brand-400 text-base">{{ $currency }}{{ number_format($product->current_price) }}</span>
                @if($product->is_on_sale)
                <span class="text-xs text-gray-400 line-through ml-1">{{ $currency }}{{ number_format($product->price) }}</span>
                @endif
            </div>
            @auth
            <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="bg-brand-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-brand-800 transition" {{ $product->stock == 0 ? 'disabled' : '' }}>
                    {{ $product->stock == 0 ? 'Out of Stock' : 'Add to Cart' }}
                </button>
            </form>
            @else
            <a href="{{ route('login') }}" class="bg-brand-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-brand-800 transition">Add to Cart</a>
            @endauth
        </div>
    </div>
</div>

