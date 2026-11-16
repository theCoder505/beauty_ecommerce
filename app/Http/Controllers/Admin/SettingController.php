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
        $deliveryCharges = $this->settingService->getDeliveryCharges();
        return view('admin.settings.index', compact('settings', 'deliveryCharges'));
    }

    public function update(Request $request)
    {
        $encryptedKeys = ['stripe_secret_key', 'stripe_webhook_secret', 'google_client_secret'];

        // Handle Site Logo Upload (Point 3)
        if ($request->hasFile('site_logo')) {
            $request->validate([
                'site_logo' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            ]);
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            $this->settingService->set('site_logo', $logoPath, false, 'general');
        } elseif ($request->boolean('remove_site_logo')) {
            $this->settingService->set('site_logo', '', false, 'general');
        }

        // Handle Site Icon / Favicon Upload (Point 3)
        if ($request->hasFile('site_icon')) {
            $request->validate([
                'site_icon' => 'image|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
            ]);
            $iconPath = $request->file('site_icon')->store('settings', 'public');
            $this->settingService->set('site_icon', $iconPath, false, 'general');
        } elseif ($request->boolean('remove_site_icon')) {
            $this->settingService->set('site_icon', '', false, 'general');
        }

        // Validate Timezone if submitted
        if ($request->filled('timezone')) {
            $request->validate([
                'timezone' => ['required', 'string', 'in:' . implode(',', \DateTimeZone::listIdentifiers())],
            ]);
        }

        // Handle Delivery Charges
        if ($request->has('delivery_charges')) {
            $chargesInput = $request->input('delivery_charges', []);
            $cleanCharges = [];
            if (is_array($chargesInput)) {
                $hasDefault = false;
                foreach ($chargesInput as $idx => $charge) {
                    $title = trim($charge['title'] ?? '');
                    if ($title === '') continue;
                    $amount = max(0, (float) ($charge['amount'] ?? 0));
                    $isDefault = !empty($charge['is_default']);
                    if ($isDefault) $hasDefault = true;
                    $cleanCharges[] = [
                        'id' => 'delivery_' . ($idx + 1) . '_' . \Illuminate\Support\Str::slug($title),
                        'title' => $title,
                        'amount' => $amount,
                        'is_default' => $isDefault,
                    ];
                }
                if (!$hasDefault && count($cleanCharges) > 0) {
                    $cleanCharges[0]['is_default'] = true;
                }
            }
            $this->settingService->set('delivery_charges', json_encode($cleanCharges), false, 'shipping');
        }

        $data = $request->except(['_token', '_method', 'site_logo', 'site_icon', 'remove_site_logo', 'remove_site_icon', 'delivery_charges']);

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

        // Reconfigure runtime timezone
        $this->settingService->configureTimezone();

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

