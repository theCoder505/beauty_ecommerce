@props([
    'name' => 'timezone',
    'id' => 'timezone-picker',
    'selected' => null,
    'label' => 'System & Store Timezone',
    'help' => 'Sets the operational timezone for the entire application (orders, reports, invoices, audit trails, and customer views). Both PHP and JavaScript will format dates and times in this timezone.'
])

@php
    $selectedTz = $selected ?? app(\App\Services\SettingService::class)->getAppTimezone();
    $timezonesFlat = \App\Services\SettingService::getTimezonesFlat();
    $timezonesGrouped = \App\Services\SettingService::getTimezonesGrouped();

    // Find current label
    $currentLabel = $selectedTz;
    foreach ($timezonesFlat as $item) {
        if ($item['identifier'] === $selectedTz) {
            $currentLabel = $item['label'];
            break;
        }
    }
@endphp

<div class="p-6 rounded-2xl border-2 border-brand-200 dark:border-brand-800 bg-white dark:bg-gray-900 shadow-md space-y-5"
     x-data="{
        open: false,
        selected: '{{ $selectedTz }}',
        selectedLabel: '{{ addslashes($currentLabel) }}',
        search: '',
        allTimezones: @js($timezonesFlat),
        liveClock: '',
        liveDate: '',
        utcOffset: '',
        get filteredTimezones() {
            if (!this.search || !this.search.trim()) {
                return this.allTimezones;
            }
            const q = this.search.toLowerCase().trim();
            return this.allTimezones.filter(tz => tz.search.includes(q));
        },
        selectTimezone(identifier, label) {
            this.selected = identifier;
            this.selectedLabel = label;
            this.open = false;
            this.search = '';
            this.updateLiveTime();
        },
        updateLiveTime() {
            try {
                const now = new Date();
                const tz = this.selected || 'UTC';
                
                this.liveClock = new Intl.DateTimeFormat(undefined, {
                    timeZone: tz,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                }).format(now);

                this.liveDate = new Intl.DateTimeFormat(undefined, {
                    timeZone: tz,
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                }).format(now);

                const parts = new Intl.DateTimeFormat('en-US', {
                    timeZone: tz,
                    timeZoneName: 'shortOffset'
                }).formatToParts(now);
                const tzPart = parts.find(p => p.type === 'timeZoneName');
                this.utcOffset = tzPart ? tzPart.value : '';
            } catch (e) {
                this.liveClock = new Date().toLocaleTimeString();
                this.liveDate = new Date().toLocaleDateString();
                this.utcOffset = '';
            }
        },
        init() {
            this.updateLiveTime();
            setInterval(() => this.updateLiveTime(), 1000);
        }
     }">

    {{-- Hidden Select for Standard Form Submission & Validation --}}
    <select name="{{ $name }}" id="{{ $id }}" x-model="selected" class="hidden" required>
        @foreach($timezonesGrouped as $groupName => $tzList)
            <optgroup label="{{ $groupName }}">
                @foreach($tzList as $item)
                    <option value="{{ $item['identifier'] }}" {{ $selectedTz === $item['identifier'] ? 'selected' : '' }}>
                        {{ $item['label'] }}
                    </option>
                @endforeach
            </optgroup>
        @endforeach
    </select>

    {{-- Component Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-brand-100 dark:border-brand-900/60 pb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-700 text-white flex items-center justify-center shrink-0 shadow-md ring-4 ring-brand-700/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>{{ $label }}</span>
                    <span class="text-[10px] font-bold text-brand-700 dark:text-brand-300 bg-brand-100 dark:bg-brand-900/50 px-2 py-0.5 rounded-full border border-brand-200 dark:border-brand-700">Active</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $help }}</p>
            </div>
        </div>

        {{-- Live Clock Pill Badge --}}
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-brand-700 text-white font-mono text-xs font-bold shadow-md ring-2 ring-brand-700/30 self-start sm:self-auto">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span x-text="liveClock">--:--:--</span>
            <span class="text-brand-200 text-[10px]" x-text="'(' + (utcOffset || selected) + ')'"></span>
        </div>
    </div>

    {{-- Active Timezone Prominent Preview Card --}}
    <div class="p-4 rounded-2xl bg-gradient-to-r from-brand-100/90 via-brand-50 to-indigo-50/70 dark:from-brand-950/80 dark:via-gray-900 dark:to-slate-900 border-2 border-brand-400/80 dark:border-brand-600 shadow-md">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-3 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-brand-200 dark:border-brand-700 shadow-xs">
                <span class="text-brand-700 dark:text-brand-400 font-bold block text-[11px] uppercase tracking-wider">Active System Timezone:</span>
                <span class="font-extrabold text-gray-900 dark:text-white font-mono text-sm block mt-0.5" x-text="selected"></span>
                <span class="text-[10px] text-gray-500 font-medium block truncate" x-text="selectedLabel"></span>
            </div>
            <div class="p-3 rounded-xl bg-white/90 dark:bg-gray-800/90 border border-brand-200 dark:border-brand-700 shadow-xs">
                <span class="text-brand-700 dark:text-brand-400 font-bold block text-[11px] uppercase tracking-wider">Active Local Date:</span>
                <span class="font-bold text-gray-900 dark:text-white text-sm block mt-0.5" x-text="liveDate"></span>
                <span class="text-[10px] text-gray-500 block">Synchronized with PHP & JS</span>
            </div>
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border-2 border-emerald-300 dark:border-emerald-700 shadow-xs">
                <span class="text-emerald-800 dark:text-emerald-300 font-bold block text-[11px] uppercase tracking-wider">Live System Clock:</span>
                <span class="font-black text-emerald-700 dark:text-emerald-300 font-mono text-base block mt-0.5" x-text="liveClock"></span>
                <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 font-mono" x-text="utcOffset + ' Offset'"></span>
            </div>
        </div>
    </div>

    {{-- Searchable Select Box Component --}}
    <div class="space-y-1.5" @click.outside="open = false" @keydown.escape.window="open = false">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
            Choose Timezone From List
        </label>

        <div class="relative">
            {{-- Select Box Trigger Button --}}
            <button type="button"
                    @click="open = !open; if(open) { $nextTick(() => $refs.dropdownSearchInput.focus()); }"
                    :class="open ? 'ring-2 ring-brand-600 border-brand-600 bg-white dark:bg-gray-800 shadow-md' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-brand-500'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl border text-left text-sm font-semibold transition focus:outline-none shadow-xs cursor-pointer">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-600 shrink-0"></span>
                    <span class="truncate text-gray-900 dark:text-white font-bold" x-text="selectedLabel"></span>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-2">
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-brand-100 text-brand-800 dark:bg-brand-900/50 dark:text-brand-200 border border-brand-200 dark:border-brand-700" x-text="utcOffset || 'UTC'"></span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>

            {{-- Dropdown Menu with Integrated Search Box Inside --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                 style="display: none;"
                 class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-gray-900 rounded-2xl border-2 border-brand-400 dark:border-brand-600 shadow-2xl overflow-hidden">
                
                {{-- Search Box inside the select dropdown --}}
                <div class="p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/80">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text"
                               x-ref="dropdownSearchInput"
                               x-model="search"
                               placeholder="Type city or country (e.g. Dhaka, London, New York, Tokyo, Dubai, Paris)..."
                               class="w-full pl-9 pr-8 py-2.5 rounded-xl border-2 border-brand-300 dark:border-brand-700 bg-white dark:bg-gray-900 text-xs font-semibold text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-brand-600 focus:border-brand-600 focus:outline-none transition">
                        <button type="button"
                                x-show="search.length > 0"
                                @click="search = ''; $refs.dropdownSearchInput.focus()"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="flex items-center justify-between mt-2 px-1 text-[11px] text-gray-500">
                        <span>Showing <strong class="text-brand-700 dark:text-brand-400 font-bold" x-text="filteredTimezones.length"></strong> of <strong x-text="allTimezones.length"></strong> timezones</span>
                        <span class="text-[10px] text-gray-400">Click any option to set</span>
                    </div>
                </div>

                {{-- Scrollable Results List with Prominent Active Item Background --}}
                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 p-1.5 scrollbar-thin">
                    <template x-for="item in filteredTimezones" :key="item.identifier">
                        <button type="button"
                                @click="selectTimezone(item.identifier, item.label)"
                                :class="selected === item.identifier 
                                    ? 'bg-brand-700 text-white font-bold shadow-md ring-2 ring-brand-800 dark:ring-brand-500' 
                                    : 'text-gray-700 dark:text-gray-200 hover:bg-brand-50 hover:text-brand-900 dark:hover:bg-brand-900/40 dark:hover:text-brand-200'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-left text-xs transition-colors my-0.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0"
                                      :class="selected === item.identifier ? 'bg-white' : 'bg-gray-300 dark:bg-gray-600'"></span>
                                <span class="truncate" x-text="item.label"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-2">
                                <span class="text-[10px] px-1.5 py-0.5 rounded"
                                      :class="selected === item.identifier ? 'bg-white/20 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500'"
                                      x-text="item.group"></span>
                                <template x-if="selected === item.identifier">
                                    <span class="text-[11px] font-bold text-white bg-brand-800 px-2 py-0.5 rounded-md flex items-center gap-1 shadow-xs">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        Active
                                    </span>
                                </template>
                            </div>
                        </button>
                    </template>

                    {{-- Empty Search Results State --}}
                    <div x-show="filteredTimezones.length === 0" class="p-6 text-center text-xs text-gray-500">
                        <p class="font-semibold text-gray-700 dark:text-gray-300">No timezones match "<span x-text="search"></span>"</p>
                        <p class="text-[11px] text-gray-400 mt-1">Try searching by continent, city, or UTC offset (e.g. Dhaka, London, UTC+6).</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-[11px] text-gray-400 flex items-center justify-between pt-1">
            <span>Includes all official IANA timezones on Earth with live search.</span>
            <span class="font-mono text-gray-500 font-semibold">Active: <span class="text-brand-700 dark:text-brand-400 font-bold" x-text="selected"></span></span>
        </p>
    </div>
</div>
