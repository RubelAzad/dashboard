<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const CACHE_KEY = 'settings.all';

    /**
     * Default values used when a setting has not been stored yet.
     *
     * @var array<string, string|null>
     */
    public const DEFAULTS = [
        'app_name' => 'Basic Admin',
        'timezone' => 'UTC',
        'logo_path' => null,
    ];

    /**
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());

        return array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function timezone(): string
    {
        return static::get('timezone') ?: config('app.timezone');
    }

    /**
     * Format a timestamp for display in the configured timezone.
     */
    public static function localTime(?CarbonInterface $date): ?string
    {
        return $date?->copy()->timezone(static::timezone())->format('Y-m-d H:i');
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }
}
