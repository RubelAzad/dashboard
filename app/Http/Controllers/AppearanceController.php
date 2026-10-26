<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Appearance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppearanceController extends Controller
{
    /**
     * Save the signed-in user's own theme. Allowed for everyone unless the organization turned it off.
     */
    public function updateMine(Request $request): RedirectResponse
    {
        abort_unless(Appearance::allowUserThemes(), 403, 'Personal themes are turned off for this organization.');

        $user = $request->user();
        $user->preferences = [...($user->preferences ?? []), 'appearance' => $request->validate(Appearance::rules())];
        $user->save();

        return back();
    }

    /**
     * Go back to the organization default.
     */
    public function resetMine(Request $request): RedirectResponse
    {
        $user = $request->user();
        $preferences = $user->preferences ?? [];
        unset($preferences['appearance']);
        $user->preferences = $preferences ?: null;
        $user->save();

        return back();
    }

    /**
     * Organization default theme. Route middleware requires "appearance.update" (Super Admin by default).
     */
    public function updateDefault(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            ...Appearance::rules('appearance.'),
            'allow_user_themes' => ['required', 'boolean'],
        ]);

        $before = [...Appearance::defaults(), 'allow_user_themes' => Appearance::allowUserThemes()];

        Setting::set('appearance', json_encode(Appearance::clean($validated['appearance'])));
        Setting::set('allow_user_themes', $request->boolean('allow_user_themes') ? '1' : '0');

        ActivityLogger::audit('updated', 'settings', null, 'Appearance', $before, [
            ...Appearance::defaults(),
            'allow_user_themes' => Appearance::allowUserThemes(),
        ]);

        return back()->with('success', 'Default appearance saved.');
    }
}
