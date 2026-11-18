@php
    $initSizes = old('sizes', isset($product) ? $product->sizes->map(fn($o) => [
        'id' => $o->id,
        'name' => $o->name,
        'is_available' => (bool)$o->is_available
    ])->values()->all() : []);

    $initColors = old('colors', isset($product) ? $product->colors->map(fn($o) => [
        'id' => $o->id,
        'name' => $o->name,
        'code' => $o->code ?? '#e11d48',
        'is_available' => (bool)$o->is_available
    ])->values()->all() : []);

    $initShades = old('shades', isset($product) ? $product->shades->map(fn($o) => [
        'id' => $o->id,
        'name' => $o->name,
        'code' => $o->code ?? '#d4a373',
        'is_available' => (bool)$o->is_available
    ])->values()->all() : []);
@endphp

<div x-data="{
    activeTab: 'sizes',
    sizes: @js($initSizes),
    colors: @js($initColors),
    shades: @js($initShades),
    addSize(name = '') {
        this.sizes.push({ id: null, name: name, is_available: true });
    },
    removeSize(index) {
        this.sizes.splice(index, 1);
    },
    addColor(name = '', code = '#e11d48') {
        this.colors.push({ id: null, name: name, code: code, is_available: true });
    },
    removeColor(index) {
        this.colors.splice(index, 1);
    },
    addShade(name = '', code = '#d4a373') {
        this.shades.push({ id: null, name: name, code: code, is_available: true });
    },
    removeShade(index) {
        this.shades.splice(index, 1);
    }
}" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 space-y-6 shadow-sm">
    <div class="border-b border-gray-100 dark:border-gray-800 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <span>Product Options & Variants</span>
                <span class="text-xs font-normal text-gray-500">(Sizes, Colors, Shades)</span>
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Add variants and mark any specific size, color, or shade as available or unavailable.</p>
        </div>
        <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl text-xs font-semibold">
            <button type="button" @click="activeTab = 'sizes'"
                :class="activeTab === 'sizes' ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <span>Sizes</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="sizes.length > 0 ? 'bg-brand-100 dark:bg-brand-900/60 text-brand-800 dark:text-brand-300 font-bold' : 'bg-gray-200 dark:bg-gray-700 text-gray-600'" x-text="sizes.length"></span>
            </button>
            <button type="button" @click="activeTab = 'colors'"
                :class="activeTab === 'colors' ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <span>Colors</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="colors.length > 0 ? 'bg-brand-100 dark:bg-brand-900/60 text-brand-800 dark:text-brand-300 font-bold' : 'bg-gray-200 dark:bg-gray-700 text-gray-600'" x-text="colors.length"></span>
            </button>
            <button type="button" @click="activeTab = 'shades'"
                :class="activeTab === 'shades' ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                <span>Shades</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="shades.length > 0 ? 'bg-brand-100 dark:bg-brand-900/60 text-brand-800 dark:text-brand-300 font-bold' : 'bg-gray-200 dark:bg-gray-700 text-gray-600'" x-text="shades.length"></span>
            </button>
        </div>
    </div>

    {{-- TAB 1: SIZES --}}
    <div x-show="activeTab === 'sizes'" class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Sizes List</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400 hidden sm:inline">Quick suggestions:</span>
                <button type="button" @click="addSize('30ml')" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">+ 30ml</button>
                <button type="button" @click="addSize('50ml')" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">+ 50ml</button>
                <button type="button" @click="addSize('100ml')" class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 transition">+ 100ml</button>
                <button type="button" @click="addSize()" class="text-xs px-3 py-1.5 bg-brand-700 hover:bg-brand-800 text-white rounded-xl font-semibold transition flex items-center gap-1">
                    <span>+ Add Size</span>
                </button>
            </div>
        </div>

        <template x-if="sizes.length === 0">
            <div class="py-6 text-center text-xs text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                No sizes added yet. Click &ldquo;+ Add Size&rdquo; above if this product has size options.
            </div>
        </template>

        <div class="space-y-2.5">
            <template x-for="(opt, index) in sizes" :key="index">
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <input type="hidden" :name="'sizes[' + index + '][id]'" :value="opt.id">
                    <div class="flex-1">
                        <input type="text" :name="'sizes[' + index + '][name]'" x-model="opt.name" required
                            placeholder="e.g. 50ml, 100ml, Travel Size, Standard"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    {{-- Availability Switch --}}
                    <div class="shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer gap-2">
                            <input type="checkbox" :name="'sizes[' + index + '][is_available]'" value="1"
                                x-model="opt.is_available" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="text-xs font-semibold whitespace-nowrap min-w-[75px]"
                                :class="opt.is_available ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                x-text="opt.is_available ? 'Available' : 'Unavailable'"></span>
                        </label>
                    </div>

                    {{-- Delete --}}
                    <button type="button" @click="removeSize(index)" class="p-2 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Remove size">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 2: COLORS --}}
    <div x-show="activeTab === 'colors'" class="space-y-4" x-cloak>
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Colors List</span>
            <div class="flex items-center gap-2">
                <button type="button" @click="addColor()" class="text-xs px-3 py-1.5 bg-brand-700 hover:bg-brand-800 text-white rounded-xl font-semibold transition flex items-center gap-1">
                    <span>+ Add Color</span>
                </button>
            </div>
        </div>

        <template x-if="colors.length === 0">
            <div class="py-6 text-center text-xs text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                No colors added yet. Click &ldquo;+ Add Color&rdquo; above if this product has color options.
            </div>
        </template>

        <div class="space-y-2.5">
            <template x-for="(opt, index) in colors" :key="index">
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <input type="hidden" :name="'colors[' + index + '][id]'" :value="opt.id">
                    
                    {{-- Color swatch preview and picker --}}
                    <div class="shrink-0 flex items-center gap-1.5">
                        <input type="color" :name="'colors[' + index + '][code]'" x-model="opt.code"
                            class="w-8 h-8 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-700 p-0.5 bg-white">
                    </div>

                    <div class="flex-1">
                        <input type="text" :name="'colors[' + index + '][name]'" x-model="opt.name" required
                            placeholder="e.g. Ruby Red, Rose Petal, Velvet Plum"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    {{-- Availability Switch --}}
                    <div class="shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer gap-2">
                            <input type="checkbox" :name="'colors[' + index + '][is_available]'" value="1"
                                x-model="opt.is_available" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="text-xs font-semibold whitespace-nowrap min-w-[75px]"
                                :class="opt.is_available ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                x-text="opt.is_available ? 'Available' : 'Unavailable'"></span>
                        </label>
                    </div>

                    {{-- Delete --}}
                    <button type="button" @click="removeColor(index)" class="p-2 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Remove color">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- TAB 3: SHADES --}}
    <div x-show="activeTab === 'shades'" class="space-y-4" x-cloak>
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Shades List</span>
            <div class="flex items-center gap-2">
                <button type="button" @click="addShade()" class="text-xs px-3 py-1.5 bg-brand-700 hover:bg-brand-800 text-white rounded-xl font-semibold transition flex items-center gap-1">
                    <span>+ Add Shade</span>
                </button>
            </div>
        </div>

        <template x-if="shades.length === 0">
            <div class="py-6 text-center text-xs text-gray-400 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                No shades added yet. Click &ldquo;+ Add Shade&rdquo; above if this product has foundation/powder/lipstick shade options.
            </div>
        </template>

        <div class="space-y-2.5">
            <template x-for="(opt, index) in shades" :key="index">
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40">
                    <input type="hidden" :name="'shades[' + index + '][id]'" :value="opt.id">

                    {{-- Shade color swatch preview and picker --}}
                    <div class="shrink-0 flex items-center gap-1.5">
                        <input type="color" :name="'shades[' + index + '][code]'" x-model="opt.code"
                            class="w-8 h-8 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-700 p-0.5 bg-white">
                    </div>

                    <div class="flex-1">
                        <input type="text" :name="'shades[' + index + '][name]'" x-model="opt.name" required
                            placeholder="e.g. 110 Porcelain, 120 Classic Ivory, 220 Natural Beige"
                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    {{-- Availability Switch --}}
                    <div class="shrink-0">
                        <label class="relative inline-flex items-center cursor-pointer gap-2">
                            <input type="checkbox" :name="'shades[' + index + '][is_available]'" value="1"
                                x-model="opt.is_available" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="text-xs font-semibold whitespace-nowrap min-w-[75px]"
                                :class="opt.is_available ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                                x-text="opt.is_available ? 'Available' : 'Unavailable'"></span>
                        </label>
                    </div>

                    {{-- Delete --}}
                    <button type="button" @click="removeShade(index)" class="p-2 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Remove shade">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>
