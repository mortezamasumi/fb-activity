<?php

namespace Mortezamasumi\FbActivity;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Mortezamasumi\FbEssentials\Facades\FbPersian;

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
}
