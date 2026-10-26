<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Writes activity and audit entries. Audit entries are recorded explicitly by controllers so a
 * single action (e.g. editing a user and their roles) becomes one entry with a readable diff.
 */
final class ActivityLogger
{
    /**
     * Attribute values that must never be stored; only the fact that they changed is kept.
     */
    private const MASKED = ['password'];

    /**
     * @param  array<string, mixed>  $properties
     */
    public static function activity(string $event, string $description, ?User $causer = null, array $properties = []): ActivityLog
    {
        return self::write(ActivityLog::ACTIVITY, $event, $description, $causer ?? Auth::user(), [
            'properties' => $properties ?: null,
        ]);
    }

    /**
     * Record a create/update/delete. For updates only the changed keys are stored, and
     * nothing is written when nothing changed.
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public static function audit(string $event, string $subjectType, ?int $subjectId, string $subjectLabel, array $old = [], array $new = []): ?ActivityLog
    {
        if ($event === 'updated') {
            $changed = array_keys(array_filter(
                $new,
                fn ($value, $key) => ! array_key_exists($key, $old) || $old[$key] !== $value,
                ARRAY_FILTER_USE_BOTH,
            ));

            if ($changed === []) {
                return null;
            }

            $old = array_intersect_key($old, array_flip($changed));
            $new = array_intersect_key($new, array_flip($changed));
        }

        // e.g. "Updated user Jane Doe"
        $description = ucfirst("{$event} ".Str::lower(Str::headline($subjectType))." {$subjectLabel}");

        return self::write(ActivityLog::AUDIT, $event, $description, Auth::user(), [
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'properties' => ['old' => self::mask($old), 'new' => self::mask($new)],
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function mask(array $values): array
    {
        foreach (self::MASKED as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = $values[$key] === null ? null : '(changed)';
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private static function write(string $logName, string $event, string $description, ?User $causer, array $attributes): ActivityLog
    {
        $request = request();

        return ActivityLog::create([
            'log_name' => $logName,
            'event' => $event,
            'description' => $description,
            'causer_id' => $causer?->id,
            'causer_name' => $causer?->name,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            ...$attributes,
        ]);
    }
}
