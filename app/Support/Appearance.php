<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

/**
 * Theme options. Each option becomes a `data-<key>` attribute on <html>; the matching CSS lives in
 * resources/css/app.css, and labels/previews for the pickers in resources/js/lib/appearance.js.
 * The organization default is stored as the "appearance" setting; users may override it in
 * `users.preferences.appearance` when "allow_user_themes" is on.
 */
final class Appearance
{
    /** @var array<string, list<string>> */
    public const OPTIONS = [
        'design' => ['midnight', 'daylight', 'floating'],
        'accent' => ['indigo', 'violet', 'blue', 'teal', 'emerald', 'amber', 'rose', 'graphite'],
        'neutral' => ['slate', 'zinc', 'stone'],
        'font' => ['inter', 'jakarta', 'manrope', 'dm-sans', 'system'],
        'icons' => ['light', 'regular', 'bold'],
        'radius' => ['sharp', 'soft', 'round'],
        'density' => ['comfortable', 'compact'],
    ];

    /** @var array<string, string> */
    public const DEFAULTS = [
        'design' => 'midnight',
        'accent' => 'indigo',
        'neutral' => 'slate',
        'font' => 'inter',
        'icons' => 'regular',
        'radius' => 'soft',
        'density' => 'comfortable',
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function rules(string $prefix = ''): array
    {
        return collect(self::OPTIONS)
            ->mapWithKeys(fn (array $values, string $key) => ["{$prefix}{$key}" => ['required', 'string', 'in:'.implode(',', $values)]])
            ->all();
    }

    /**
     * Organization default, with unknown or missing keys replaced by built-in defaults.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return self::clean(json_decode((string) Setting::get('appearance'), true) ?: []);
    }

    public static function allowUserThemes(): bool
    {
        return Setting::get('allow_user_themes', '1') === '1';
    }

    /**
     * The user's own overrides, or null when they have none.
     *
     * @return array<string, string>|null
     */
    public static function forUser(?User $user): ?array
    {
        $saved = $user?->preferences['appearance'] ?? null;

        return is_array($saved) ? self::clean($saved) : null;
    }

    /**
     * What this user should see right now.
     *
     * @return array<string, string>
     */
    public static function effective(?User $user): array
    {
        return (self::allowUserThemes() ? self::forUser($user) : null) ?? self::defaults();
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function clean(array $values): array
    {
        $clean = self::DEFAULTS;

        foreach (self::OPTIONS as $key => $allowed) {
            if (isset($values[$key]) && in_array($values[$key], $allowed, true)) {
                $clean[$key] = $values[$key];
            }
        }

        return $clean;
    }
}
