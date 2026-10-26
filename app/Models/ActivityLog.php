<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per logged event. "activity" rows are sign-in/account events; "audit" rows are
 * data changes made through the dashboard and carry {old, new} values in `properties`.
 */
class ActivityLog extends Model
{
    use MassPrunable;

    public const ACTIVITY = 'activity';

    public const AUDIT = 'audit';

    /** Rows older than this are removed by the scheduled `model:prune` command. */
    public const RETENTION_DAYS = 365;

    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
