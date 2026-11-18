<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mews\Purifier\Facades\Purifier;

class LegalPageController extends Controller
{
    public function __construct(protected SettingService $settingService) {}

    /**
     * Show the edit page for Terms & Conditions.
     */
    public function editTerms(): View
    {
        $content = Setting::where('key', 'terms_and_conditions')->value('value') ?? '';
        return view('admin.legal.terms', compact('content'));
    }

    /**
     * Clean and sanitize rich HTML while preserving headings, lists, formatting, links, and images.
     */
    protected function cleanHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        return Purifier::clean($html);
    }

    /**
     * Update Terms & Conditions content.
     */
    public function updateTerms(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $raw = $request->input('content', '');
        $clean = $this->cleanHtml($raw);

        $this->settingService->set('terms_and_conditions', $clean, false, 'legal');

        return redirect()->route('admin.terms.edit')->with('success', 'Terms & Conditions updated successfully.');
    }

    /**
     * Show the edit page for Privacy Policy.
     */
    public function editPrivacy(): View
    {
        $content = Setting::where('key', 'privacy_policy')->value('value') ?? '';
        return view('admin.legal.privacy', compact('content'));
    }

    /**
     * Update Privacy Policy content.
     */
    public function updatePrivacy(Request $request): RedirectResponse
    {
        $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        $raw = $request->input('content', '');
        $clean = $this->cleanHtml($raw);

        $this->settingService->set('privacy_policy', $clean, false, 'legal');

        return redirect()->route('admin.privacy.edit')->with('success', 'Privacy Policy updated successfully.');
    }
}
