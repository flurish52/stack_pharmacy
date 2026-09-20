<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['user', 'type', 'event', 'from', 'to']);

        $activities = Activity::query()
            ->with([
                'causer:id,name',
                // include soft-deleted subjects so "deleted" rows still show a name
                'subject' => fn (MorphTo $morph) => $morph->withTrashed(),
            ])
            ->when($filters['user'] ?? null, function ($q, $user) {
                $user === 'system' ? $q->whereNull('causer_id') : $q->where('causer_id', $user);
            })
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('log_name', $type))
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->latest('id')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (Activity $activity) => [
                'id' => $activity->id,
                'when' => $activity->created_at,
                'actor' => $activity->causer?->name ?? 'System',
                'log_name' => $activity->log_name,
                'event' => $activity->event,
                'description' => $activity->description,
                'type' => $activity->subject_type ? Str::headline(class_basename($activity->subject_type)) : null,
                'subject' => $this->subjectLabel($activity),
                'changes' => $this->changes($activity),
            ]);

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'user' => $filters['user'] ?? '',
                'type' => $filters['type'] ?? '',
                'event' => $filters['event'] ?? '',
                'from' => $filters['from'] ?? '',
                'to' => $filters['to'] ?? '',
            ],
            'users' => User::whereIn(
                'id',
                Activity::query()->whereNotNull('causer_id')->distinct()->select('causer_id')
            )->orderBy('name')->get(['id', 'name']),
            'types' => Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name'),
            'events' => ['created', 'updated', 'deleted', 'restored'],
        ]);
    }

    /** A readable name for what the action was done to. */
    private function subjectLabel(Activity $activity): ?string
    {
        foreach (['name', 'title', 'variant_name', 'handle', 'email'] as $field) {
            if ($activity->subject && filled($activity->subject->{$field})) {
                return (string) $activity->subject->{$field};
            }
        }

        // Subject was permanently deleted: fall back to what was recorded.
        foreach (['name', 'title', 'variant_name', 'handle'] as $field) {
            $value = data_get($activity->properties, "attributes.{$field}") ?? data_get($activity->properties, "old.{$field}");

            if (filled($value)) {
                return (string) $value;
            }
        }

        return $activity->subject_id ? "#{$activity->subject_id}" : null;
    }

    /** Normalised list of {field, from, to} for the details row. */
    private function changes(Activity $activity): array
    {
        $props = $activity->properties;
        $new = (array) $props->get('attributes', []);
        $old = (array) $props->get('old', []);

        if ($new || $old) {
            return collect(array_unique([...array_keys($new), ...array_keys($old)]))
                ->map(fn ($field) => [
                    'field' => $field,
                    'from' => $this->scalar($old[$field] ?? null),
                    'to' => $this->scalar($new[$field] ?? null),
                ])->values()->all();
        }

        // Manual logs, e.g. staff role changes: {from: 'staff', to: 'admin'}
        if ($props->has('from') && $props->has('to')) {
            return [['field' => 'role', 'from' => $this->scalar($props->get('from')), 'to' => $this->scalar($props->get('to'))]];
        }

        return $props->map(fn ($value, $key) => ['field' => $key, 'from' => null, 'to' => $this->scalar($value)])
            ->values()->all();
    }

    private function scalar(mixed $value): string|int|float|bool|null
    {
        return is_scalar($value) || $value === null ? $value : json_encode($value);
    }
}
