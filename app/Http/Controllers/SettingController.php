<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::allValues();

        return Inertia::render('Settings/Index', [
            'settings' => [
                'app_name' => $settings['app_name'],
                'timezone' => $settings['timezone'],
                'logo_url' => $settings['logo_path'] ? Storage::disk('public')->url($settings['logo_path']) : null,
            ],
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'timezone:all'],
            // SVG is excluded on purpose: it can carry scripts.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['boolean'],
        ]);

        Setting::set('app_name', $validated['app_name']);
        Setting::set('timezone', $validated['timezone']);

        $currentLogo = Setting::get('logo_path');

        if ($request->hasFile('logo') || $request->boolean('remove_logo')) {
            if ($currentLogo) {
                Storage::disk('public')->delete($currentLogo);
            }

            Setting::set('logo_path', $request->hasFile('logo')
                ? $request->file('logo')->store('branding', 'public')
                : null);
        }

        return back()->with('success', 'Settings saved.');
    }
}
