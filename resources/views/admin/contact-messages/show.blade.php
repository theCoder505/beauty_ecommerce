@extends('admin.layouts.app')
@section('title', 'Inquiry: ' . $contactMessage->subject)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.contact-messages.index') }}" class="p-2 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $contactMessage->subject }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Contact Inquiry Details & Email Reply</p>
            </div>
        </div>

        <div>
            @if($contactMessage->status === 'replied')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Replied
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Pending Response
                </span>
            @endif
        </div>
    </div>

    {{-- Sender & Metadata Card --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-gray-500 block">Sender Name</span>
                <span class="font-bold text-sm text-gray-900 dark:text-white mt-0.5 block">{{ $contactMessage->name }}</span>
            </div>
            <div>
                <span class="text-gray-500 block">Verified Email (OTP Validated)</span>
                <a href="mailto:{{ $contactMessage->email }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline mt-0.5 block">
                    {{ $contactMessage->email }} ↗
                </a>
            </div>
            <div>
                <span class="text-gray-500 block">Received At</span>
                <span class="font-medium text-gray-900 dark:text-gray-100 mt-0.5 block">
                    {{ $contactMessage->created_at->format('M d, Y – h:i A') }}
                    ({{ $contactMessage->created_at->diffForHumans() }})
                </span>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 block mb-2">Inquiry Message:</span>
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800 text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line">
{{ $contactMessage->message }}
            </div>
        </div>
    </div>

    {{-- Existing Reply (if already replied) --}}
    @if($contactMessage->admin_reply)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 p-6 shadow-sm">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">Previous Reply Sent</h3>
            </div>
            <span class="text-xs text-gray-500">
                Sent {{ $contactMessage->replied_at ? $contactMessage->replied_at->format('M d, Y h:i A') : '' }}
                @if($contactMessage->repliedByAdmin)
                    by <strong class="text-gray-700 dark:text-gray-300">{{ $contactMessage->repliedByAdmin->name }}</strong>
                @endif
            </span>
        </div>
        <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line">
{{ $contactMessage->admin_reply }}
        </div>
    </div>
    @endif

    {{-- Reply Form --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
        <h3 class="font-bold text-base text-gray-900 dark:text-white mb-1">
            {{ $contactMessage->admin_reply ? 'Send Another Reply' : 'Reply to ' . $contactMessage->name }}
        </h3>
        <p class="text-xs text-gray-500 mb-4">
            This response will be sent directly to <strong>{{ $contactMessage->email }}</strong> and recorded in this inquiry log.
        </p>

        <form method="POST" action="{{ route('admin.contact-messages.reply', $contactMessage->id) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Reply Message *</label>
                <textarea name="reply" rows="6" required placeholder="Type your response to the customer here..."
                    class="w-full px-4 py-3 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-gray-400">Recipient: {{ $contactMessage->email }}</span>
                <button type="submit" class="px-6 py-2.5 bg-brand-700 hover:bg-brand-800 text-white rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Reply via Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
