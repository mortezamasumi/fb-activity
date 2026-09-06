<?php

namespace Mortezamasumi\FbActivity\Support;

use Closure;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Mortezamasumi\FbActivity\Contracts\HasActivityTitle;
use Spatie\Activitylog\Models\Activity;
use Throwable;

class ActivitySubjectResolver
{
    /** @var array<string, ?string> */
    protected array $memo = [];

    /**
     * Resolve a human title for the activity's subject.
     */
    public function subjectTitle(?Activity $activity): ?string
    {
        $subjectType = $activity?->getAttribute('subject_type');

        if (blank($subjectType)) {
            return null;
        }

        $key = $this->memoKey('subject', $subjectType, $activity->getAttribute('subject_id'));

        if (! array_key_exists($key, $this->memo)) {
            $this->memo[$key] = $this->resolveTitle(
                $activity,
                $subjectType,
                $activity->getAttribute('subject_id'),
                $this->relationFor($activity, 'subject', $subjectType),
                'subject',
            );
        }

        return $this->memo[$key];
    }

    /**
     * Resolve a human title for the activity's causer.
     */
    public function causerTitle(?Activity $activity): ?string
    {
        $causerType = $activity?->getAttribute('causer_type');

        if (blank($causerType)) {
            return null;
        }

        $key = $this->memoKey('causer', $causerType, $activity->getAttribute('causer_id'));

        if (! array_key_exists($key, $this->memo)) {
            $this->memo[$key] = $this->resolveTitle(
                $activity,
                $causerType,
                $activity->getAttribute('causer_id'),
                $this->relationFor($activity, 'causer', $causerType),
                'causer',
            );
        }

        return $this->memo[$key];
    }

    /**
     * Resolve a short human label for a model type ("Patient", "Podcast", ...).
     */
    public function modelLabel(?string $modelType): string
    {
        if (blank($modelType)) {
            return '-';
        }

        $key = $this->memoKey('label', $modelType, null);

        if (! array_key_exists($key, $this->memo)) {
            $this->memo[$key] = $this->resolveLabel($modelType);
        }

        return (string) $this->memo[$key];
    }

    /**
     * Resolve a URL to the subject's own page, or null when not linkable.
     */
    public function subjectUrl(?Activity $activity): ?string
    {
        if (! config('fb-activity.subject.link.enabled', true)) {
            return null;
        }

        $subjectType = $activity?->getAttribute('subject_type');
        $subjectId = $activity?->getAttribute('subject_id');

        if (blank($subjectType) || blank($subjectId)) {
            return null;
        }

        $key = $this->memoKey('url', $subjectType, $subjectId);

        if (! array_key_exists($key, $this->memo)) {
            $this->memo[$key] = $this->resolveUrl($activity, $subjectType, $subjectId);
        }

        return $this->memo[$key];
    }

    protected function resolveTitle(Activity $activity, string $modelType, mixed $modelId, ?Model $record, string $group): ?string
    {
        $candidates = $this->titleCandidates($activity, $modelType, $record, $group);

        foreach ($candidates as $candidate) {
            $resolved = $this->guard(fn () => $this->evaluateCandidate($candidate, $record, $activity, $modelId));

            if ($resolved !== null && $this->isUsableTitle($resolved)) {
                return trim((string) $resolved);
            }
        }

        return null;
    }

    /**
     * Build the ordered candidate list for a title resolution (spec D1).
     *
     * @return array<int, Closure|string>
     */
    protected function titleCandidates(Activity $activity, string $modelType, ?Model $record, string $group): array
    {
        $candidates = [];

        // 1. Per-model config override.
        $override = config("fb-activity.{$group}.titles.".ltrim($modelType, '\\'));

        if (array_key_exists(ltrim($modelType, '\\'), (array) config("fb-activity.{$group}.titles", []))) {
            if ($override !== null) {
                $candidates[] = $override;
            }
        }

        // 2. HasActivityTitle contract.
        if ($record instanceof HasActivityTitle) {
            $candidates[] = fn (Model $subject, Activity $act): ?string => $subject instanceof HasActivityTitle
                ? $subject->activityTitle($act)
                : null;
        }

        // 3. Filament record title.
        if (config('fb-activity.subject.use_filament_record_title', true)) {
            $candidates[] = fn (): ?string => $this->filamentRecordTitle($record, $modelType);
        }

        // 4. Attribute cascade.
        foreach ((array) config("fb-activity.{$group}.attribute_cascade", []) as $attribute) {
            $candidates[] = $attribute;
        }

        // 5. Deleted-record recovery from the activity properties.
        if (
            $group === 'subject' &&
            config('fb-activity.subject.recover_deleted', true) &&
            $record === null
        ) {
            foreach (['attributes', 'old'] as $propertyKey) {
                $attributes = $this->guard(fn () => $activity->getExtraProperty($propertyKey));

                if (! is_array($attributes)) {
                    continue;
                }

                foreach ((array) config('fb-activity.subject.attribute_cascade', []) as $attribute) {
                    $candidates[] = fn (): ?string => $this->pickArrayTitle($attributes, $attribute);
                }
            }
        }

        // 6. Final fallback "Label #id" is handled by the caller, not a candidate.

        return $candidates;
    }

    protected function evaluateCandidate(mixed $candidate, ?Model $record, Activity $activity, mixed $modelId): ?string
    {
        if ($candidate instanceof Closure) {
            $result = $candidate($record, $activity);
        } elseif (is_string($candidate) && class_exists($candidate) && method_exists($candidate, '__invoke')) {
            $result = app($candidate)($record, $activity);
        } elseif (is_string($candidate)) {
            $result = $record === null ? null : data_get($record, $candidate);
        } else {
            $result = null;
        }

        if (is_array($result)) {
            return null;
        }

        return $result === null ? null : (string) $result;
    }

    protected function filamentRecordTitle(?Model $record, string $modelType): ?string
    {
        if ($record === null || ! $this->filamentAvailable()) {
            return null;
        }

        $resource = $this->filamentResourceFor($modelType);

        if ($resource === null) {
            return null;
        }

        $title = $resource::getRecordTitle($record);

        if (blank($title)) {
            return null;
        }

        // getRecordTitle falls back to the model label when the record title
        // attribute is missing - treat that as a miss so the cascade continues.
        $titleString = $title instanceof Htmlable
            ? $title->toHtml()
            : $title;

        if ($titleString === $resource::getModelLabel()) {
            return null;
        }

        return $titleString;
    }

    protected function resolveLabel(string $modelType): string
    {
        $modelType = ltrim($modelType, '\\');

        // 1. Per-model config label (plain string or translation key).
        $configured = config('fb-activity.subject.labels.'.$modelType);

        if (filled($configured) && is_string($configured)) {
            return (string) __($configured);
        }

        // 2. Filament resource label.
        if ($this->filamentAvailable()) {
            $resource = $this->filamentResourceFor($modelType);

            if ($resource !== null) {
                $label = $this->guard(fn () => $resource::getModelLabel());

                if (filled($label)) {
                    return (string) $label;
                }
            }
        }

        // 3. Class basename headline.
        return Str::of($modelType)->afterLast('\\')->headline()->toString();
    }

    protected function resolveUrl(Activity $activity, string $subjectType, mixed $subjectId): ?string
    {
        $configured = config('fb-activity.subject.urls.'.ltrim($subjectType, '\\'));

        if ($configured !== null) {
            $url = $this->guard(function () use ($configured, $activity, $subjectId, $subjectType) {
                $record = $this->relationFor($activity, 'subject', $subjectType);

                if ($configured instanceof Closure) {
                    return $configured($record, $activity);
                }

                if (is_string($configured) && class_exists($configured) && method_exists($configured, '__invoke')) {
                    return app($configured)($record, $activity);
                }

                if (is_string($configured)) {
                    if (str_contains($configured, '{id}')) {
                        return str_replace('{id}', (string) $subjectId, $configured);
                    }

                    return route($configured, ['record' => $subjectId]);
                }

                return null;
            });

            return $this->isUsableUrl($url) ? $url : null;
        }

        // Filament resource view page.
        if ($this->filamentAvailable()) {
            $resource = $this->filamentResourceFor($subjectType);

            if ($resource !== null) {
                $url = $this->guard(fn () => $resource::getUrl('view', ['record' => $subjectId]));

                if ($this->isUsableUrl($url)) {
                    return $url;
                }
            }
        }

        return null;
    }

    /**
     * Find the first Filament resource (across registered panels) managing the
     * given model type.
     *
     * @return class-string<resource>|null
     */
    protected function filamentResourceFor(string $modelType): ?string
    {
        if (! $this->filamentAvailable()) {
            return null;
        }

        return $this->guard(function () use ($modelType): ?string {
            foreach (Filament::getPanels() as $panel) {
                foreach ($panel->getResources() as $resource) {
                    if (is_a($resource::getModel(), $modelType, true)) {
                        return $resource;
                    }
                }
            }

            return null;
        });
    }

    /**
     * Safely fetch the morph relation, returning null when the related class
     * does not exist (deleted/renamed model) or the record is gone.
     */
    protected function relationFor(Activity $activity, string $relation, string $morphType): ?Model
    {
        if (! class_exists($morphType) || ! is_subclass_of($morphType, Model::class)) {
            return null;
        }

        /** @var ?Model $record */
        $record = $this->guard(fn () => $activity->getRelationValue($relation));

        return $record instanceof Model ? $record : null;
    }

    protected function filamentAvailable(): bool
    {
        return $this->guard(fn (): bool => class_exists(Filament::class) && filled(Filament::getPanels()), false) ?? false;
    }

    /**
     * Run a resolution step, reporting and swallowing any Throwable so the
     * resolver never throws (spec D1).
     */
    protected function guard(callable $callback, mixed $default = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable $exception) {
            report($exception);

            return $default;
        }
    }

    protected function isUsableTitle(mixed $value): bool
    {
        return is_string($value) && filled(trim($value));
    }

    protected function isUsableUrl(mixed $value): bool
    {
        return is_string($value) && filled(trim($value));
    }

    /**
     * Pick a title-ish value out of a property attributes array by attribute,
     * applying the same usability rules.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function pickArrayTitle(array $attributes, string $attribute): ?string
    {
        $value = Arr::get($attributes, $attribute);

        if (is_array($value)) {
            return null;
        }

        $value = $value === null ? null : (string) $value;

        return $this->isUsableTitle($value) ? trim((string) $value) : null;
    }

    protected function memoKey(string $prefix, ?string $type, mixed $id): string
    {
        return implode('|', [$prefix, ltrim((string) $type, '\\'), (string) $id, app()->getLocale()]);
    }
}
