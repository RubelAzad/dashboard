<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:activity-logs.view', only: ['activity']),
            new Middleware('permission:audit-logs.view', only: ['audit']),
        ];
    }

    /**
     * Every logged event: sign-ins and account events plus data changes.
     */
    public function activity(Request $request): Response
    {
        $filters = $this->filters($request, ['type']);

        $query = $this->filtered($filters)
            ->when(in_array($filters['type'], [ActivityLog::ACTIVITY, ActivityLog::AUDIT], true),
                fn (Builder $query) => $query->where('log_name', $filters['type']));

        return Inertia::render('Logs/Activity', [
            'logs' => $this->paginate($query),
            'filters' => $filters,
            'events' => $this->events(),
        ]);
    }

    /**
     * Data changes with before/after values.
     */
    public function audit(Request $request): Response
    {
        $filters = $this->filters($request, ['subject']);

        $query = $this->filtered($filters)
            ->where('log_name', ActivityLog::AUDIT)
            ->when($filters['subject'] !== '', fn (Builder $query) => $query->where('subject_type', $filters['subject']));

        return Inertia::render('Logs/Audit', [
            'logs' => $this->paginate($query),
            'filters' => $filters,
            'events' => $this->events(ActivityLog::AUDIT),
            'subjects' => ActivityLog::where('log_name', ActivityLog::AUDIT)->whereNotNull('subject_type')
                ->distinct()->orderBy('subject_type')->pluck('subject_type'),
        ]);
    }

    /**
     * @param  list<string>  $extra
     * @return array<string, string>
     */
    private function filters(Request $request, array $extra): array
    {
        $keys = ['search', 'event', 'from', 'to', ...$extra];

        $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return collect($keys)->mapWithKeys(fn ($key) => [$key => trim((string) $request->query($key, ''))])->all();
    }

    /**
     * @param  array<string, string>  $filters
     */
    private function filtered(array $filters): Builder
    {
        // Date filters are entered in the dashboard's timezone; stored timestamps are in app time.
        $timezone = Setting::timezone();

        return ActivityLog::query()
            ->when($filters['search'] !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('description', 'like', "%{$filters['search']}%")
                ->orWhere('causer_name', 'like', "%{$filters['search']}%")
                ->orWhere('subject_label', 'like', "%{$filters['search']}%")
                ->orWhere('ip_address', 'like', "%{$filters['search']}%")))
            ->when($filters['event'] !== '', fn (Builder $query) => $query->where('event', $filters['event']))
            ->when($filters['from'] !== '', fn (Builder $query) => $query->where('created_at', '>=',
                Carbon::parse($filters['from'], $timezone)->startOfDay()->timezone(config('app.timezone'))))
            ->when($filters['to'] !== '', fn (Builder $query) => $query->where('created_at', '<=',
                Carbon::parse($filters['to'], $timezone)->endOfDay()->timezone(config('app.timezone'))));
    }

    private function paginate(Builder $query): mixed
    {
        return $query->latest('created_at')->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ActivityLog $log) => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'event' => $log->event,
                'description' => $log->description,
                'causer' => $log->causer_name ? ['id' => $log->causer_id, 'name' => $log->causer_name] : null,
                'subject_type' => $log->subject_type,
                'subject_label' => $log->subject_label,
                'properties' => $log->properties,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => Setting::localTime($log->created_at),
                'created_at_iso' => $log->created_at?->toIso8601String(),
            ]);
    }

    /**
     * @return list<string>
     */
    private function events(?string $logName = null): array
    {
        return ActivityLog::query()
            ->when($logName, fn (Builder $query) => $query->where('log_name', $logName))
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->all();
    }
}
