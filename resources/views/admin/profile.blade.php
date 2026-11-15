@extends('admin.layouts.app')
@section('title', 'Admin Profile Settings')

@section('content')
<div class="space-y-6 max-w-5xl">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Admin Profile & Security</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your administrator account details, security credentials, and view active sessions.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Profile Card --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm">
                <h2 class="font-semibold text-base text-gray-900 dark:text-white mb-4">Profile Details</h2>

                <form id="admin-profile-form" action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    {{-- Avatar with preview (Point 9) --}}
                    <div class="flex flex-col items-center text-center p-3">
                        <div class="relative group w-24 h-24 rounded-full overflow-hidden border-2 border-brand-500 shadow-md mb-3 bg-gray-100 dark:bg-gray-800">
                            <img id="admin-avatar-preview" src="{{ $admin->avatar_url }}" class="w-full h-full object-cover preview-target" alt="{{ $admin->name }}">
                        </div>
                        <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-300 hover:bg-brand-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Change Photo
                            <input type="file" name="avatar" accept="image/*" data-preview="#admin-avatar-preview" class="hidden">
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Full Name</label>
                        <input type="text" name="name" id="admin-name-input" value="{{ $admin->name }}" required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Current Email</label>
                        <div class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-300 font-mono truncate" id="admin-current-email-badge">
                            {{ $admin->email }}
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Change email below via OTP verification.</p>
                    </div>

                    <button type="submit" id="btn-update-profile" class="w-full bg-brand-700 hover:bg-brand-800 text-white font-semibold py-2.5 rounded-xl transition text-xs shadow-sm flex items-center justify-center gap-2">
                        <span>Save Profile</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Security (Change Email & Change Password) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Change Email Card (Point 8 & Point 7) --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm" x-data="{ emailOtpSent: false, cooldown: 0 }">
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-base text-gray-900 dark:text-white">Change Email Address</h2>
                        <p class="text-xs text-gray-500">Requires OTP verification sent to your current email: <strong class="text-gray-700 dark:text-gray-300" id="current-email-text">{{ $admin->email }}</strong></p>
                    </div>
                </div>

                {{-- Step 1: Input New Email --}}
                <div x-show="!emailOtpSent" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">New Email Address</label>
                        <input type="email" id="admin-new-email" placeholder="new.email@example.com"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>
                    <button type="button" @click="sendEmailOtp($data)" id="btn-send-email-otp"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-xs shadow-sm flex items-center gap-2">
                        <span>Send Verification OTP</span>
                    </button>
                </div>

                {{-- Step 2: Verify OTP via AJAX --}}
                <div x-show="emailOtpSent" x-cloak class="space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-center justify-between">
                        <span>Verification code sent to current email: <strong id="sent-email-target">{{ $admin->email }}</strong></span>
                        <button type="button" @click="emailOtpSent = false" class="text-amber-700 dark:text-amber-400 underline font-semibold text-xs ml-2">Edit</button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Enter 6-Digit OTP</label>
                        <input type="text" id="admin-email-otp" maxlength="6" placeholder="• • • • • •"
                            class="w-full max-w-xs px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center text-lg font-bold tracking-[0.4em] focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="verifyEmailOtp($data)" id="btn-verify-email-otp"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-xs shadow-sm">
                            Verify & Update Email
                        </button>
                        <button type="button" @click="resendAdminOtp('email', $data)" :disabled="cooldown > 0"
                            class="text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-300 disabled:opacity-50">
                            <span x-text="cooldown > 0 ? 'Resend in ' + cooldown + 's' : 'Resend OTP'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Change Password Card (Point 8 & Point 7) --}}
            <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm" x-data="{ passOtpSent: false, cooldown: 0, showPass: false }">
                <div class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-800 pb-4 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-base text-gray-900 dark:text-white">Change Account Password</h2>
                        <p class="text-xs text-gray-500">Requires OTP authorization sent to your current email address.</p>
                    </div>
                </div>

                {{-- Step 1: Input New Password --}}
                <div x-show="!passOtpSent" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">New Password</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" id="admin-new-password" placeholder="Minimum 8 characters"
                                    class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Confirm New Password</label>
                            <input :type="showPass ? 'text' : 'password'" id="admin-new-password-confirmation" placeholder="Repeat new password"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                        </div>
                    </div>
                    <button type="button" @click="sendPasswordOtp($data)" id="btn-send-pass-otp"
                        class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-xs shadow-sm flex items-center gap-2">
                        <span>Send Verification OTP</span>
                    </button>
                </div>

                {{-- Step 2: Verify Password OTP via AJAX --}}
                <div x-show="passOtpSent" x-cloak class="space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs rounded-xl flex items-center justify-between">
                        <span>Verification code sent to email: <strong id="sent-pass-target">{{ $admin->email }}</strong></span>
                        <button type="button" @click="passOtpSent = false" class="text-amber-700 dark:text-amber-400 underline font-semibold text-xs ml-2">Edit</button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Enter 6-Digit OTP</label>
                        <input type="text" id="admin-pass-otp" maxlength="6" placeholder="• • • • • •"
                            class="w-full max-w-xs px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-center text-lg font-bold tracking-[0.4em] focus:ring-2 focus:ring-brand-600 focus:outline-none">
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" @click="verifyPasswordOtp($data)" id="btn-verify-pass-otp"
                            class="bg-brand-700 hover:bg-brand-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-xs shadow-sm">
                            Verify & Change Password
                        </button>
                        <button type="button" @click="resendAdminOtp('password', $data)" :disabled="cooldown > 0"
                            class="text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-brand-700 dark:hover:text-brand-300 disabled:opacity-50">
                            <span x-text="cooldown > 0 ? 'Resend in ' + cooldown + 's' : 'Resend OTP'"></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Login History Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Last 10 Login Sessions</h2>
            <span class="text-xs text-gray-400">Security audit log</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase bg-gray-50 dark:bg-gray-800/60 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-5 py-3 text-left">IP Address</th>
                        <th class="px-5 py-3 text-left">Browser / Device</th>
                        <th class="px-5 py-3 text-right">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logins as $l)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $l->ip_address }}</td>
                        <td class="px-5 py-3 text-xs text-gray-500 truncate max-w-md">{{ $l->user_agent }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400 text-right">{{ $l->created_at->format('M j, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-5 py-6 text-center text-gray-400 text-xs">No recorded logins yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Profile Form AJAX submission
    document.getElementById('admin-profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-update-profile');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Saving…';

        const formData = new FormData(this);

        fetch("{{ route('admin.profile.update') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.toast) toast(data.message, 'success');
                if (data.avatar_url) {
                    document.getElementById('admin-avatar-preview').src = data.avatar_url;
                }
            } else {
                if (window.toast) toast(data.message || 'Update failed', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('An error occurred while saving profile.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    // 1. Send Email OTP (AJAX)
    function sendEmailOtp(alpineData) {
        const newEmail = document.getElementById('admin-new-email').value.trim();
        if (!newEmail) {
            if (window.toast) toast('Please enter a new email address.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-send-email-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Sending OTP…';

        fetch("{{ route('admin.profile.send-email-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ new_email: newEmail })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                alpineData.emailOtpSent = true;
                startAdminCooldown(alpineData);
                if (window.toast) toast(body.message, 'success');
            } else {
                if (window.toast) toast(body.message || (body.errors ? Object.values(body.errors)[0][0] : 'Failed to send OTP'), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Failed to connect to server.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    // Verify Email OTP (AJAX)
    function verifyEmailOtp(alpineData) {
        const otp = document.getElementById('admin-email-otp').value.trim();
        if (!otp || otp.length !== 6) {
            if (window.toast) toast('Please enter the 6-digit OTP code.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-verify-email-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Verifying…';

        fetch("{{ route('admin.profile.verify-email') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ otp: otp })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Updated!',
                    text: body.message,
                    confirmButtonColor: '#15803d'
                });
                alpineData.emailOtpSent = false;
                document.getElementById('admin-current-email-badge').textContent = body.email;
                document.getElementById('current-email-text').textContent = body.email;
                document.getElementById('sent-email-target').textContent = body.email;
                document.getElementById('admin-new-email').value = '';
                document.getElementById('admin-email-otp').value = '';
            } else {
                if (window.toast) toast(body.message || 'Verification failed', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Verification failed.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    // 2. Send Password OTP (AJAX)
    function sendPasswordOtp(alpineData) {
        const newPassword = document.getElementById('admin-new-password').value;
        const confirmPassword = document.getElementById('admin-new-password-confirmation').value;

        if (!newPassword || newPassword.length < 8) {
            if (window.toast) toast('Password must be at least 8 characters.', 'warning');
            return;
        }
        if (newPassword !== confirmPassword) {
            if (window.toast) toast('Passwords do not match.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-send-pass-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Sending OTP…';

        fetch("{{ route('admin.profile.send-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                alpineData.passOtpSent = true;
                startAdminCooldown(alpineData);
                if (window.toast) toast(body.message, 'success');
            } else {
                if (window.toast) toast(body.message || (body.errors ? Object.values(body.errors)[0][0] : 'Failed to send OTP'), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Failed to connect to server.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    // Verify Password OTP (AJAX)
    function verifyPasswordOtp(alpineData) {
        const otp = document.getElementById('admin-pass-otp').value.trim();
        if (!otp || otp.length !== 6) {
            if (window.toast) toast('Please enter the 6-digit OTP code.', 'warning');
            return;
        }

        const btn = document.getElementById('btn-verify-pass-otp');
        const origText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Verifying…';

        fetch("{{ route('admin.profile.verify-change') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ otp: otp })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Password Updated!',
                    text: body.message,
                    confirmButtonColor: '#15803d'
                });
                alpineData.passOtpSent = false;
                document.getElementById('admin-new-password').value = '';
                document.getElementById('admin-new-password-confirmation').value = '';
                document.getElementById('admin-pass-otp').value = '';
            } else {
                if (window.toast) toast(body.message || 'Verification failed', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Verification failed.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origText;
        });
    }

    // Resend OTP helper for Admin
    function resendAdminOtp(type, alpineData) {
        if (alpineData.cooldown > 0) return;

        fetch("{{ route('admin.profile.resend-otp') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ type: type })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                startAdminCooldown(alpineData);
                if (window.toast) toast(body.message, 'success');
            } else {
                if (window.toast) toast(body.message || 'Cannot resend OTP at this time.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            if (window.toast) toast('Resend failed.', 'error');
        });
    }

    function startAdminCooldown(alpineData) {
        alpineData.cooldown = 60;
        const interval = setInterval(() => {
            alpineData.cooldown--;
            if (alpineData.cooldown <= 0) {
                clearInterval(interval);
            }
        }, 1000);
    }
</script>
@endpush
