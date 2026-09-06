<?php

namespace Mortezamasumi\FbActivity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mortezamasumi\FbActivity\Support\ActivitySubjectResolver;
use Mortezamasumi\FbEssentials\Facades\FbPersian;
use Spatie\Activitylog\Models\Activity;

class FbActivity
{
    public function getSubjectName(?Model $record, ?string $state): ?string
    {
        if (empty($state)) {
            return '-';
        }

        return Str::of($state)->afterLast('\\')->headline();
    }

    public function getSubject(?Model $record, ?string $state): ?string
    {
        if (empty($state)) {
            return '-';
        }

        $subjectId = $record?->getAttribute('subject_id');

        if ($record && class_exists($state) && is_subclass_of($state, Model::class)) {
            /** @var class-string<Model> $state */
            $subjectModel = $state::query()->whereKey($subjectId)->first();

            $subjectName = $subjectModel?->getAttribute('name')
                ?? $subjectModel?->getAttribute('title')
                ?? $subjectModel?->getAttribute('text')
                ?? '-';
        } else {
            $subjectName = $subjectId;
        }

        $sn = $this->getSubjectName($record, $state);

        if ($sn === '-') {
            return null;
        }

        return __('fb-activity::fb-activity.infolist.subject_name', [
            'a' => $sn,
            'b' => $subjectName ?? '-',
        ]);
    }

    /**
     * Render a stored wall-clock datetime for display, honoring the
     * `fb-activity.timezone` reinterpretation config (spec D4).
     *
     * When `timezone.storage` is null the value is rendered exactly like the
     * fb-essentials macros do today (app-timezone wall time). When set, the raw
     * stored value is parsed in the storage timezone, converted to the display
     * timezone (or the app timezone) and rendered in that zone.
     */
    public function formatDateTime(mixed $value, ?string $format = null): ?string
    {
        if (empty($value)) {
            return '';
        }

        $storageTz = config('fb-activity.timezone.storage');

        if (blank($storageTz)) {
            return FbPersian::jDateTime($format, $value);
        }

        $displayTz = config('fb-activity.timezone.display') ?: date_default_timezone_get();

        try {
            $carbon = Carbon::createFromFormat('Y-m-d H:i:s', (string) $value, $storageTz)
                ?: Carbon::parse((string) $value, $storageTz);
        } catch (\Throwable) {
            return null;
        }

        $converted = $carbon->setTimezone($displayTz);

        return FbPersian::jDateTime($format, $converted->format('Y-m-d H:i:s'), $displayTz);
    }

    /**
     * Convert a user-entered display-timezone date to the storage timezone for
     * query bounds (spec D4). Identity when no reinterpretation is configured.
     */
    public function toStorageDate(string $date): string
    {
        $storageTz = config('fb-activity.timezone.storage');

        if (blank($storageTz)) {
            return $date;
        }

        $displayTz = config('fb-activity.timezone.display') ?: date_default_timezone_get();

        try {
            return Carbon::parse($date, $displayTz)
                ->setTimezone($storageTz)
                ->toDateString();
        } catch (\Throwable) {
            return $date;
        }
    }

    /**
     * Resolve a human title for the activity's subject (spec D1). Falls back to
     * "Label #id" when every candidate misses.
     */
    public function resolveSubjectTitle(?Activity $activity): ?string
    {
        $title = $this->resolver()->subjectTitle($activity);

        if ($title !== null) {
            return $title;
        }

        $subjectType = $activity?->getAttribute('subject_type');
        $subjectId = $activity?->getAttribute('subject_id');

        if (blank($subjectType)) {
            return null;
        }

        return trim($this->subjectModelLabel($subjectType).' #'.(string) $subjectId);
    }

    /**
     * Resolve a human title for the activity's causer (spec D2). Falls back to
     * "Label #id" when every candidate misses.
     */
    public function resolveCauserTitle(?Activity $activity): ?string
    {
        $title = $this->resolver()->causerTitle($activity);

        if ($title !== null) {
            return $title;
        }

        $causerType = $activity?->getAttribute('causer_type');
        $causerId = $activity?->getAttribute('causer_id');

        if (blank($causerType)) {
            return null;
        }

        return trim($this->resolver()->modelLabel($causerType).' #'.(string) $causerId);
    }

    /**
     * Short human label for a model type ("Patient", "Podcast", ...).
     */
    public function subjectModelLabel(?string $subjectType): string
    {
        return $this->resolver()->modelLabel($subjectType);
    }

    /**
     * URL to the subject's own page, or null when not linkable (spec D3).
     */
    public function resolveSubjectUrl(?Activity $activity): ?string
    {
        return $this->resolver()->subjectUrl($activity);
    }

    protected function resolver(): ActivitySubjectResolver
    {
        return app(ActivitySubjectResolver::class);
    }
}
