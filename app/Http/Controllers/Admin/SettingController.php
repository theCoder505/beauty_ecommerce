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
                'site_logo' => ['file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:2048'],
            ]);
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            $this->settingService->set('site_logo', $logoPath, false, 'general');
        } elseif ($request->boolean('remove_site_logo')) {
            $this->settingService->set('site_logo', '', false, 'general');
        }

        // Handle Site Icon / Favicon Upload (Point 3)
        if ($request->hasFile('site_icon')) {
            $request->validate([
                'site_icon' => ['file', 'mimes:jpeg,png,jpg,gif,svg,webp,ico,avif', 'max:1024'],
            ]);
            $iconPath = $request->file('site_icon')->store('settings', 'public');
            $this->settingService->set('site_icon', $iconPath, false, 'general');
        } elseif ($request->boolean('remove_site_icon')) {
            $this->settingService->set('site_icon', '', false, 'general');
        }

        // Handle Hero Section Right-Side Image Upload
        if ($request->hasFile('hero_image')) {
            $request->validate([
                'hero_image' => ['file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:5120'],
            ]);
            $heroPath = $request->file('hero_image')->store('settings', 'public');
            $this->settingService->set('hero_image', $heroPath, false, 'general');
        } elseif ($request->boolean('remove_hero_image')) {
            $this->settingService->set('hero_image', '', false, 'general');
        }

        // Validate Timezone if submitted
        if ($request->filled('timezone')) {
            $request->validate([
                'timezone' => ['required', 'string', 'in:' . implode(',', \DateTimeZone::listIdentifiers())],
            ]);
        }

        // Validate Return Window Days
        if ($request->has('return_time_limit_days')) {
            $request->validate([
                'return_time_limit_days' => ['nullable', 'integer', 'min:1', 'max:365'],
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

        $data = $request->except(['_token', '_method', 'site_logo', 'site_icon', 'remove_site_logo', 'remove_site_icon', 'hero_image', 'remove_hero_image', 'delivery_charges']);

        foreach ($data as $key => $value) {
            if ($value === null) continue;

            if (in_array($key, ['terms_and_conditions', 'privacy_policy'])) {
                $value = ($value !== '') ? \Mews\Purifier\Facades\Purifier::clean($value) : '';
            }

            $isEncrypted = in_array($key, $encryptedKeys);
            $group = $this->resolveGroup($key);
            $this->settingService->set($key, $value, $isEncrypted, $group);
        }

        // Handle booleans (checkboxes not submitted = false)
        $boolKeys = ['cod_enabled', 'manual_payment_enabled', 'stripe_enabled', 'allow_review_updates'];
        foreach ($boolKeys as $bKey) {
            $group = in_array($bKey, ['allow_review_updates']) ? 'policy' : 'payment';
            $this->settingService->set($bKey, $request->has($bKey) ? '1' : '0', false, $group);
        }

        // Reconfigure runtime timezone
        $this->settingService->configureTimezone();

        return redirect()->route('admin.settings.index')->with('success', 'Settings saved.');
    }

    protected function resolveGroup(string $key): string
    {
        if (in_array($key, ['terms_and_conditions', 'privacy_policy'])) return 'legal';
        if (in_array($key, ['return_time_limit_days', 'allow_review_updates'])) return 'policy';
        if (str_starts_with($key, 'stripe_')) return 'stripe';
        if (str_starts_with($key, 'google_')) return 'google';
        if (in_array($key, ['cod_enabled', 'manual_payment_enabled', 'stripe_enabled', 'manual_payment_instructions'])) return 'payment';
        if (in_array($key, ['facebook_url', 'instagram_url', 'twitter_url', 'youtube_url'])) return 'social';
        if (str_starts_with($key, 'default_meta') || $key === 'site_name') return 'seo';
        return 'general';
    }
}

