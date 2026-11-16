@extends('users.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 flex flex-col lg:flex-row gap-8" x-data="profileAddressManager({
    addresses: @js($addresses),
    defaultName: @js($user->name),
    defaultPhone: @js($user->phone ?? ''),
    googleMapsKey: @js($googleMapsKey ?? '')
})">
    @include('users.partials.dashboard-sidebar')

    <main class="flex-1 min-w-0 space-y-8">
        {{-- Page Heading --}}
        <div>
            <h1 class="font-serif text-3xl font-bold text-gray-900 dark:text-white">Account Profile</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your personal information and multiple delivery addresses</p>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-2xl px-5 py-4 text-sm flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-2xl px-5 py-4 text-sm space-y-1 shadow-xs">
            <p class="font-semibold">Please check the following errors:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- Section 1: Personal Details Card --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-8 border border-stone-200/90 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between pb-5 mb-6 border-b border-stone-200/80 dark:border-gray-700/80">
                <div>
                    <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white">Personal Information</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Update your basic profile credentials</p>
                </div>
            </div>

            <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                {{-- Avatar --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-5 pb-6 border-b border-stone-100 dark:border-gray-700/60">
                    <div class="relative shrink-0">
                        <img id="user-avatar-preview" src="{{ $user->avatar_url }}" class="w-20 h-20 rounded-2xl object-cover border-2 border-brand-700/30 dark:border-brand-500/30 shadow-md preview-target">
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-brand-700 text-white flex items-center justify-center text-xs shadow">✓</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1">Profile Photo</label>
                        <p class="text-xs text-gray-400 mb-2.5">JPG, PNG or WebP up to 2MB</p>
                        <input type="file" name="avatar" accept="image/*" data-preview="#user-avatar-preview" class="text-xs text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border file:border-brand-700/30 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-300 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="profile-input" placeholder="Your full name">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="profile-input" placeholder="+880 1XXXXXXXXX">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300">Email Address (Read-only)</label>
                            <a href="{{ route('user.change-email') }}" class="text-xs font-semibold text-brand-700 dark:text-brand-400 hover:underline">Change Email →</a>
                        </div>
                        <div class="relative">
                            <input type="email" value="{{ $user->email }}" disabled class="profile-input-disabled pl-10">
                            <span class="absolute right-3.5 top-3 text-[11px] font-medium text-gray-400 dark:text-gray-500 bg-gray-200/70 dark:bg-gray-800 px-2 py-0.5 rounded-md flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Locked
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-brand-700 text-white font-semibold text-sm px-6 py-3 rounded-xl hover:bg-brand-800 active:scale-95 transition shadow-sm">
                        Save Personal Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Section 2: Multiple Delivery Addresses & Google Map Picker --}}
        <div class="bg-white dark:bg-gray-800/90 rounded-2xl p-6 sm:p-8 border border-stone-200/90 dark:border-gray-700 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-stone-200/80 dark:border-gray-700/80">
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-serif text-xl font-bold text-gray-900 dark:text-white">Delivery Addresses</h2>
                        <span class="text-xs font-bold bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 px-2 py-0.5 rounded-full" x-text="addressesList.length + ' saved'">
                            {{ $addresses->count() }} saved
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Set multiple address lines. The primary address is automatically selected during checkout.
                    </p>
                </div>

                <button type="button"
                        @click="openAddModal()"
                        class="inline-flex items-center gap-2 bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition shadow-sm self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add New Address</span>
                </button>
            </div>

            {{-- Address Cards Grid --}}
            <template x-if="addressesList.length > 0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="addr in addressesList" :key="addr.id">
                        <div class="rounded-2xl border transition-all duration-200 p-5 flex flex-col justify-between"
                             :class="addr.is_primary ? 'border-brand-700 bg-brand-50/30 dark:bg-brand-900/10 shadow-sm ring-1 ring-brand-700/30' : 'border-stone-200 dark:border-gray-700 bg-stone-50/40 dark:bg-gray-800/40 hover:border-stone-300 dark:hover:border-gray-600'">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-md"
                                              :class="addr.label.toLowerCase() === 'home' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : (addr.label.toLowerCase() === 'office' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-stone-200 text-stone-800 dark:bg-gray-700 dark:text-gray-200')">
                                            <span x-text="addr.label"></span>
                                        </span>
                                        <template x-if="addr.is_primary">
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-100/80 dark:bg-emerald-950/40 px-2 py-0.5 rounded-md">
                                                ★ Primary
                                            </span>
                                        </template>
                                    </div>

                                    <template x-if="addr.latitude && addr.longitude">
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center gap-0.5" title="Pinned on Map">
                                            <svg class="w-3 h-3 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                                            GPS Pinned
                                        </span>
                                    </template>
                                </div>

                                <p class="font-semibold text-sm text-gray-900 dark:text-white" x-text="addr.recipient_name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2" x-text="addr.phone"></p>

                                <p class="text-xs text-gray-700 dark:text-gray-300 leading-relaxed font-medium mb-1" x-text="addr.address_line"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span x-text="addr.city"></span><span x-show="addr.state" x-text="', ' + addr.state"></span><span x-show="addr.postal_code" x-text="' ' + addr.postal_code"></span>, <span x-text="addr.country"></span>
                                </p>
                            </div>

                            <div class="pt-4 mt-4 border-t border-stone-200/80 dark:border-gray-700/80 flex items-center justify-between text-xs font-semibold">
                                <div>
                                    <template x-if="!addr.is_primary">
                                        <form :action="'{{ url('dashboard/addresses') }}/' + addr.id + '/primary'" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-brand-700 dark:text-brand-400 hover:underline">
                                                Set as Primary
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="addr.is_primary">
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                                            ✓ Default for Checkout
                                        </span>
                                    </template>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="button" @click="openEditModal(addr)" class="text-gray-600 dark:text-gray-300 hover:text-brand-700 dark:hover:text-brand-400 transition">
                                        Edit
                                    </button>
                                    <form :action="'{{ url('dashboard/addresses') }}/' + addr.id" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 dark:hover:text-red-400 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="addressesList.length === 0">
                <div class="text-center py-12 px-4 rounded-2xl border-2 border-dashed border-stone-200 dark:border-gray-700 bg-stone-50/50 dark:bg-gray-800/30">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-brand-50 dark:bg-brand-900/30 flex items-center justify-center text-brand-700 dark:text-brand-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">No addresses saved yet</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-4">Add your home or office delivery address with our interactive map locator to make checking out effortless.</p>
                    <button type="button" @click="openAddModal()" class="inline-flex items-center gap-1.5 bg-brand-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl hover:bg-brand-800 transition shadow-sm">
                        <span>+ Add Your First Address</span>
                    </button>
                </div>
            </template>
        </div>
    </main>

    {{-- Interactive Google Map & Address Modal --}}
    <div x-show="isModalOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-stone-200 dark:border-gray-700 relative max-h-[92vh] flex flex-col"
             @click.away="closeModal()">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-200 dark:border-gray-700 shrink-0">
                <div>
                    <h3 class="font-serif text-xl font-bold text-gray-900 dark:text-white" x-text="isEditing ? 'Edit Delivery Address' : 'Add New Address'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pick location on Google Map or enter address details manually</p>
                </div>
                <button type="button" @click="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body (Scrollable) --}}
            <div class="overflow-y-auto flex-1 pr-1 space-y-5">
                {{-- Google Map & Current Location Box --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-brand-700 dark:text-brand-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            Google Map Pin Locator
                        </label>

                        {{-- Geolocation Button --}}
                        <button type="button"
                                @click="locateCurrentPosition()"
                                :disabled="isLocating"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/30 hover:bg-brand-100 dark:hover:bg-brand-900/50 px-3 py-1.5 rounded-lg transition active:scale-95 border border-brand-700/20 disabled:opacity-50">
                            <svg class="w-3.5 h-3.5" :class="isLocating ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3" stroke-width="2"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v3m0 14v3M2 12h3m14 0h3"></path>
                            </svg>
                            <span x-text="isLocating ? 'Locating…' : 'Use My Current Location'"></span>
                        </button>
                    </div>

                    {{-- Map Container --}}
                    <div class="relative rounded-2xl overflow-hidden border-2 border-stone-200 dark:border-gray-700 shadow-inner bg-stone-100 dark:bg-gray-900">
                        {{-- Search Input inside map --}}
                        <div class="absolute top-2.5 left-2.5 right-2.5 z-10 flex gap-1.5">
                            <input type="text"
                                   id="map-search-input"
                                   x-model="searchQuery"
                                   @keydown.enter.prevent="searchLocationQuery()"
                                   placeholder="Search area, landmark or street…"
                                   class="flex-1 px-3 py-2 rounded-xl text-xs bg-white/95 dark:bg-gray-800/95 backdrop-blur border border-stone-300 dark:border-gray-600 shadow-md focus:outline-none focus:ring-2 focus:ring-brand-700 text-gray-900 dark:text-white">
                            <button type="button"
                                    @click="searchLocationQuery()"
                                    class="px-3 py-2 bg-brand-700 text-white rounded-xl text-xs font-bold hover:bg-brand-800 transition shadow-md shrink-0">
                                Search
                            </button>
                        </div>

                        {{-- The Map Element --}}
                        <div id="address-map" class="w-full h-64 z-0"></div>

                        {{-- Geocode Loading Overlay --}}
                        <div x-show="isGeocoding" x-cloak class="absolute inset-0 bg-black/20 backdrop-blur-xs flex items-center justify-center z-10">
                            <div class="bg-white/95 dark:bg-gray-800/95 px-3 py-1.5 rounded-full shadow-lg text-xs font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                                <span class="w-3 h-3 border-2 border-brand-700 border-t-transparent rounded-full animate-spin"></span>
                                Resolving address…
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400 px-1">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Drag pin or click map to set location
                        </span>
                        <span x-show="form.latitude && form.longitude" class="font-mono text-[10px] text-gray-400">
                            <span x-text="Number(form.latitude).toFixed(5)"></span>, <span x-text="Number(form.longitude).toFixed(5)"></span>
                        </span>
                    </div>
                </div>

                {{-- Address Form --}}
                <form :action="isEditing ? '{{ url('dashboard/addresses') }}/' + editingId : '{{ route('user.addresses.store') }}'"
                      method="POST"
                      id="address-modal-form"
                      class="space-y-4 pt-2">
                    @csrf
                    <template x-if="isEditing">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="latitude" x-model="form.latitude">
                    <input type="hidden" name="longitude" x-model="form.longitude">

                    {{-- Address Type / Label --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Address Type</label>
                        <div class="flex gap-2">
                            <template x-for="type in ['Home', 'Office', 'Other']" :key="type">
                                <button type="button"
                                        @click="form.label = type"
                                        class="flex-1 py-2 px-3 rounded-xl text-xs font-bold border transition text-center"
                                        :class="form.label.toLowerCase() === type.toLowerCase() ? 'bg-brand-700 text-white border-brand-700 shadow-xs' : 'border-stone-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-brand-700/60'">
                                    <span x-text="type"></span>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="label" x-model="form.label">
                    </div>

                    {{-- Recipient Details --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Recipient Name</label>
                            <input type="text" name="recipient_name" x-model="form.recipient_name" required class="profile-input" placeholder="Name for delivery">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Phone Number</label>
                            <input type="text" name="phone" x-model="form.phone" required class="profile-input" placeholder="Phone number">
                        </div>
                    </div>

                    {{-- Street Address --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Street Address / House / Road *</label>
                        <textarea name="address_line" x-model="form.address_line" rows="2" required class="profile-input" placeholder="House/Flat #, Road #, Sector or Area"></textarea>
                    </div>

                    {{-- City, State, Postal Code, Country --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">City *</label>
                            <input type="text" name="city" x-model="form.city" required class="profile-input" placeholder="City">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">State/Zone</label>
                            <input type="text" name="state" x-model="form.state" class="profile-input" placeholder="State/Division">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Postal Code</label>
                            <input type="text" name="postal_code" x-model="form.postal_code" class="profile-input" placeholder="Zip code">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
                            <input type="text" name="country" x-model="form.country" class="profile-input" placeholder="Country">
                        </div>
                    </div>

                    {{-- Set as Primary Checkbox --}}
                    <label class="flex items-center gap-3 cursor-pointer pt-1">
                        <input type="checkbox" name="is_primary" value="1" x-model="form.is_primary" class="w-4 h-4 text-brand-700 rounded border-stone-300 focus:ring-brand-700">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Set as primary address for 1-click checkout</span>
                    </label>

                    {{-- Form Buttons --}}
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-200 dark:border-gray-700">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border border-stone-300 dark:border-gray-600 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-stone-100 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-700 hover:bg-brand-800 text-white text-xs font-bold transition shadow-sm active:scale-95">
                            <span x-text="isEditing ? 'Update Address' : 'Save Address'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
{{-- Leaflet CSS fallback for interactive map --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.profile-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(214, 211, 209, 0.9);
    background-color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    color: #111827;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
}
.dark .profile-input {
    border-color: rgba(75, 85, 99, 0.8);
    background-color: rgba(17, 24, 39, 0.8);
    color: #f9fafb;
}
.profile-input:focus {
    outline: none;
    border-color: #8b263e;
    box-shadow: 0 0 0 2px rgba(139, 38, 62, 0.2);
}
.profile-input-disabled {
    width: 100%;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(229, 231, 235, 1);
    background-color: rgba(243, 244, 246, 0.8);
    font-size: 0.875rem;
    color: #9ca3af;
    cursor: not-allowed;
}
.dark .profile-input-disabled {
    border-color: rgba(55, 65, 81, 1);
    background-color: rgba(31, 41, 55, 0.6);
    color: #6b7280;
}
</style>
@endpush

@push('scripts')
{{-- Google Maps JS API (if key present) & Leaflet JS fallback --}}
@if(!empty($googleMapsKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsKey }}&libraries=places" async defer></script>
@endif
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
function profileAddressManager(config) {
    return {
        addressesList: config.addresses || [],
        isModalOpen: false,
        isEditing: false,
        editingId: null,
        isLocating: false,
        isGeocoding: false,
        searchQuery: '',
        mapInstance: null,
        mapMarker: null,
        mapProvider: 'leaflet', // 'google' or 'leaflet'
        defaultLat: 23.8103, // Dhaka / default central point
        defaultLng: 90.4125,

        form: {
            label: 'Home',
            recipient_name: config.defaultName || '',
            phone: config.defaultPhone || '',
            address_line: '',
            city: '',
            state: '',
            postal_code: '',
            country: 'BD',
            latitude: null,
            longitude: null,
            is_primary: false,
        },

        openAddModal() {
            this.isEditing = false;
            this.editingId = null;
            this.form = {
                label: 'Home',
                recipient_name: config.defaultName || '',
                phone: config.defaultPhone || '',
                address_line: '',
                city: '',
                state: '',
                postal_code: '',
                country: 'BD',
                latitude: null,
                longitude: null,
                is_primary: this.addressesList.length === 0,
            };
            this.isModalOpen = true;
            this.$nextTick(() => {
                this.initMap(this.defaultLat, this.defaultLng);
                // Attempt to auto-detect current location when adding
                this.locateCurrentPosition(false);
            });
        },

        openEditModal(address) {
            this.isEditing = true;
            this.editingId = address.id;
            this.form = {
                label: address.label || 'Home',
                recipient_name: address.recipient_name || '',
                phone: address.phone || '',
                address_line: address.address_line || '',
                city: address.city || '',
                state: address.state || '',
                postal_code: address.postal_code || '',
                country: address.country || 'BD',
                latitude: address.latitude || null,
                longitude: address.longitude || null,
                is_primary: Boolean(address.is_primary),
            };
            this.isModalOpen = true;
            this.$nextTick(() => {
                const lat = address.latitude ? parseFloat(address.latitude) : this.defaultLat;
                const lng = address.longitude ? parseFloat(address.longitude) : this.defaultLng;
                this.initMap(lat, lng);
            });
        },

        closeModal() {
            this.isModalOpen = false;
        },

        initMap(lat, lng) {
            const mapEl = document.getElementById('address-map');
            if (!mapEl) return;

            // Check if Google Maps is loaded and key is valid
            if (window.google && window.google.maps && config.googleMapsKey) {
                this.initGoogleMap(lat, lng, mapEl);
            } else {
                this.initLeafletMap(lat, lng, mapEl);
            }
        },

        initGoogleMap(lat, lng, container) {
            this.mapProvider = 'google';
            const position = { lat: lat, lng: lng };

            if (!this.mapInstance || !this.mapInstance.setCenter) {
                this.mapInstance = new google.maps.Map(container, {
                    center: position,
                    zoom: 15,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
                });

                this.mapMarker = new google.maps.Marker({
                    position: position,
                    map: this.mapInstance,
                    draggable: true,
                    animation: google.maps.Animation.DROP,
                });

                this.mapMarker.addListener('dragend', (e) => {
                    const newLat = e.latLng.lat();
                    const newLng = e.latLng.lng();
                    this.onLocationSelected(newLat, newLng);
                });

                this.mapInstance.addListener('click', (e) => {
                    const newLat = e.latLng.lat();
                    const newLng = e.latLng.lng();
                    this.mapMarker.setPosition(e.latLng);
                    this.onLocationSelected(newLat, newLng);
                });
            } else {
                this.mapInstance.setCenter(position);
                this.mapMarker.setPosition(position);
            }

            this.form.latitude = lat;
            this.form.longitude = lng;
        },

        initLeafletMap(lat, lng, container) {
            this.mapProvider = 'leaflet';

            if (this.mapInstance && this.mapInstance.remove) {
                this.mapInstance.remove();
                this.mapInstance = null;
            }

            this.mapInstance = L.map(container).setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.mapInstance);

            const customIcon = L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            this.mapMarker = L.marker([lat, lng], {
                draggable: true,
                icon: customIcon
            }).addTo(this.mapInstance);

            this.mapMarker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                this.onLocationSelected(pos.lat, pos.lng);
            });

            this.mapInstance.on('click', (e) => {
                this.mapMarker.setLatLng(e.latlng);
                this.onLocationSelected(e.latlng.lat, e.latlng.lng);
            });

            setTimeout(() => {
                if (this.mapInstance) this.mapInstance.invalidateSize();
            }, 300);

            this.form.latitude = lat;
            this.form.longitude = lng;
        },

        locateCurrentPosition(showAlert = true) {
            if (!navigator.geolocation) {
                if (showAlert && window.toast) toast('Geolocation is not supported by your browser.', 'error');
                return;
            }

            this.isLocating = true;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.isLocating = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.updateMapPosition(lat, lng);
                    this.onLocationSelected(lat, lng);
                    if (showAlert && window.toast) toast('Location detected from your device.', 'success');
                },
                (err) => {
                    this.isLocating = false;
                    if (showAlert) {
                        const msg = err.code === 1 ? 'Location permission was denied. Please allow location access.' : 'Unable to determine your location.';
                        if (window.toast) toast(msg, 'error');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        },

        updateMapPosition(lat, lng) {
            this.form.latitude = lat;
            this.form.longitude = lng;

            if (this.mapProvider === 'google' && this.mapInstance) {
                const pos = new google.maps.LatLng(lat, lng);
                this.mapInstance.panTo(pos);
                if (this.mapMarker) this.mapMarker.setPosition(pos);
            } else if (this.mapInstance && this.mapMarker) {
                this.mapInstance.setView([lat, lng], 16);
                this.mapMarker.setLatLng([lat, lng]);
            }
        },

        onLocationSelected(lat, lng) {
            this.form.latitude = lat;
            this.form.longitude = lng;
            this.reverseGeocode(lat, lng);
        },

        reverseGeocode(lat, lng) {
            this.isGeocoding = true;

            // If Google Maps Geocoder is available
            if (window.google && window.google.maps && window.google.maps.Geocoder) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    this.isGeocoding = false;
                    if (status === 'OK' && results[0]) {
                        this.parseGoogleGeocode(results[0]);
                    } else {
                        this.reverseGeocodeNominatim(lat, lng);
                    }
                });
                return;
            }

            // Fallback to OpenStreetMap Nominatim reverse geocode
            this.reverseGeocodeNominatim(lat, lng);
        },

        reverseGeocodeNominatim(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.isGeocoding = false;
                if (!data || !data.address) return;

                const a = data.address;
                const road = a.road || a.pedestrian || a.suburb || a.neighbourhood || '';
                const house = a.house_number ? a.house_number + ', ' : '';
                const area = a.suburb || a.neighbourhood || a.city_district || '';

                if (road || area) {
                    this.form.address_line = `${house}${road}${area ? ', ' + area : ''}`.trim();
                } else if (data.display_name) {
                    this.form.address_line = data.display_name.split(',').slice(0, 3).join(', ').trim();
                }

                this.form.city = a.city || a.town || a.county || a.state_district || 'Dhaka';
                this.form.state = a.state || '';
                this.form.postal_code = a.postcode || '';
                this.form.country = (a.country_code ? a.country_code.toUpperCase() : 'BD');
            })
            .catch(() => {
                this.isGeocoding = false;
            });
        },

        parseGoogleGeocode(place) {
            let streetNumber = '';
            let route = '';
            let sublocality = '';

            place.address_components.forEach(c => {
                const types = c.types;
                if (types.includes('street_number')) streetNumber = c.long_name;
                if (types.includes('route')) route = c.long_name;
                if (types.includes('sublocality') || types.includes('sublocality_level_1')) sublocality = c.long_name;
                if (types.includes('locality')) this.form.city = c.long_name;
                if (types.includes('administrative_area_level_1')) this.form.state = c.long_name;
                if (types.includes('postal_code')) this.form.postal_code = c.long_name;
                if (types.includes('country')) this.form.country = c.short_name;
            });

            const line = [streetNumber, route, sublocality].filter(Boolean).join(' ');
            this.form.address_line = line || place.formatted_address;
        },

        searchLocationQuery() {
            if (!this.searchQuery.trim()) return;

            this.isLocating = true;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.searchQuery)}&limit=1`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                this.isLocating = false;
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    this.updateMapPosition(lat, lng);
                    this.onLocationSelected(lat, lng);
                } else {
                    if (window.toast) toast('Location not found. Try another place.', 'info');
                }
            })
            .catch(() => {
                this.isLocating = false;
            });
        }
    };
}
</script>
@endpush
@endsection
