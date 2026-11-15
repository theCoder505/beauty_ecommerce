<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $encryptedKeys = ['stripe_secret_key', 'stripe_webhook_secret', 'google_client_secret'];

        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            if ($value === null || $value === '') continue;

            $isEncrypted = in_array($key, $encryptedKeys);
            $group = $this->resolveGroup($key);
            $this->settingService->set($key, $value, $isEncrypted, $group);
        }

        // Handle booleans (checkboxes not submitted = false)
        $boolKeys = ['cod_enabled', 'manual_payment_enabled', 'stripe_enabled'];
        foreach ($boolKeys as $bKey) {
            $this->settingService->set($bKey, $request->has($bKey) ? '1' : '0', false, 'payment');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved.');
    }

    protected function resolveGroup(string $key): string
    {
        if (str_starts_with($key, 'stripe_')) return 'stripe';
        if (str_starts_with($key, 'google_')) return 'google';
        if (in_array($key, ['cod_enabled', 'manual_payment_enabled', 'stripe_enabled', 'manual_payment_instructions'])) return 'payment';
        if (in_array($key, ['facebook_url', 'instagram_url', 'twitter_url', 'youtube_url'])) return 'social';
        if (str_starts_with($key, 'default_meta') || $key === 'site_name') return 'seo';
        return 'general';
    }
}

