# FB Activity — Filament Activity Log

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mortezamasumi/fb-activity.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-activity)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mortezamasumi/fb-activity/ci.yml?branch=main&label=tests&style=flat-square)](https://github.com/mortezamasumi/fb-activity/actions?query=branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mortezamasumi/fb-activity.svg?style=flat-square)](https://packagist.org/packages/mortezamasumi/fb-activity)
[![License](https://img.shields.io/packagist/l/mortezamasumi/fb-activity.svg?style=flat-square)](LICENSE.md)

A Filament v5 plugin that surfaces [spatie/laravel-activitylog](https://github.com/spatie/laravel-activitylog)
records as a read-only admin resource: searchable, filterable table with CSV export, an infolist
view page, Persian date handling, and Filament Shield permission integration.

---

## Features

- **Activity resource** — index and view pages backed by the `activity_log` table, with eager-loaded causer/subject relationships
- **CSV export** — Filament export action with a configurable max row limit and completion notifications
- **Table & filters** — searchable/sortable columns, a `created_at` date-range filter, and causer search across `name` / `first_name` / `last_name`
- **View page** — infolist with human-readable subject names and a rendered `properties` section
- **Log scoping** — include/exclude activity descriptions by wildcard pattern via config or env
- **Permissions** — `ViewAny:Activity`, `View:Activity`, `Delete:Activity`, `Export:Activity`, `ViewAllUsers:Activity` wired through Filament Shield
- **Localized UI** — Persian and English translations shipped out of the box

---

## Installation

```bash
composer require mortezamasumi/fb-activity
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="fb-activity-migrations"
php artisan migrate
```

Publish the config file:

```bash
php artisan vendor:publish --tag="fb-activity-config"
```

---

## Configuration

```php
// config/fb-activity.php
return [
    'navigation' => [
        'model_label' => 'fb-activity::fb-activity.navigation.label',
        'plural_model_label' => 'fb-activity::fb-activity.navigation.plural_label',
        'group' => 'fb-activity::fb-activity.navigation.group',
        'parent_item' => null,
        'icon' => 'heroicon-o-queue-list',
        'active_icon' => 'heroicon-s-queue-list',
        'badge' => false,
        'badge_tooltip' => null,
        'sort' => 20,
    ],
    'export' => [
        'exporter' => '\Mortezamasumi\FbActivity\Resources\Exports\ActivityExporter',
        'max_export_rows' => env('ACTIVITY_MAX_EXPORT_ROWS', 3000),
    ],
    'exclude_logs' => env('ACTIVITY_EXCLUDE_LOGS', null),
    'include_logs' => env('ACTIVITY_INCLUDE_LOGS', null),
    'timezone' => [
        'storage' => env('FB_ACTIVITY_STORAGE_TIMEZONE'),
        'display' => env('FB_ACTIVITY_DISPLAY_TIMEZONE'),
    ],
    'subject' => [
        'titles' => [],
        'labels' => [],
        'urls' => [],
        'attribute_cascade' => ['display_name', 'full_name', 'name', 'title'],
        'use_filament_record_title' => true,
        'show_model_label' => true,
        'recover_deleted' => true,
        'link' => ['enabled' => true],
    ],
    'causer' => [
        'titles' => [],
        'attribute_cascade' => ['display_name', 'full_name', 'name'],
    ],
    'events' => [
        'colors' => [
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
        ],
        'icons' => [],
    ],
    'logs' => [
        'colors' => [],
    ],
];
```

- `navigation.*` — panel navigation label, group, icons, badge, and sort order
- `export.max_export_rows` — maximum rows per export (default `3000`)
- `exclude_logs` / `include_logs` — comma-separated description patterns; `*` matches any characters and `?` matches a single character. Include patterns apply as `OR` (any match is kept), exclude patterns apply as `NOT LIKE` on top of the include filter
- `timezone.storage` — timezone the stored `created_at` wall-clock values were written in (e.g. `UTC` for rows produced by an environment without `APP_TIMEZONE`); `timezone.display` — timezone to render in; both `null` = default Laravel behavior
- `subject.*` / `causer.*` — smart title resolution (see below)
- `events.colors` / `events.icons` / `logs.colors` — per-event/log badge colors and icons; unknown events fall back to `primary` (events also honor a legacy `draft => gray` mapping)

### Smart subject and causer titles

The list page, view page and export render subjects and causers as human titles, not
`Model::class` + id. Resolution order (first non-empty wins, every step is failure-safe):

1. **Per-model config override** — `fb-activity.subject.titles` / `causer.titles`,
   keyed by model FQCN. Values may be an attribute/dot-path (evaluated on the model),
   a `Closure fn (Model $subject, Activity $activity): ?string`, an invokable
   class-string, or explicit `null` to skip to the cascade.
2. **`HasActivityTitle` contract** — implement
   `Mortezamasumi\FbActivity\Contracts\HasActivityTitle::activityTitle(Activity): ?string`
   on the model for full control (can branch on `$activity->event`).
3. **Filament record title** — when the model is managed by a Filament resource,
   `Resource::getRecordTitle()` / `$recordTitleAttribute` are used (disable with
   `subject.use_filament_record_title = false`).
4. **Attribute cascade** — `subject.attribute_cascade` (default
   `display_name, full_name, name, title`; causer defaults to
   `display_name, full_name, name`). Attributes are read with `getAttribute()`, so
   accessors and Spatie `HasTranslations` (per-locale JSON with fallback locale)
   resolve naturally.
5. **Deleted-subject recovery** — when the subject record no longer exists, the same
   cascade is applied to the activity's `properties.attributes` / `properties.old`
   (disable with `subject.recover_deleted = false`).
6. **Fallback** — `Label #id` (e.g. `Podcast #42`).

Short labels ("Podcast", "Patient", …) come from `subject.labels` (string or
translation key) → the Filament resource label → the class basename.

> Search on the list page matches `subject_type`/`subject_id` (SQL), not the resolved
> titles. If you want "Podcast" casing instead of Filament's kebab-case label,
> set `subject.labels[Model::class] => 'Podcast'`.

```php
use App\Models\Patient;
use Mortezamasumi\FbActivity\Contracts\HasActivityTitle;
use Spatie\Activitylog\Models\Activity;

// config/fb-activity.php
'subject' => [
    'titles' => [
        Patient::class => 'full_name',                    // attribute or dot-path
        // Patient::class => fn ($record) => $record->file_number,  // Closure
    ],
    'labels' => [
        Patient::class => 'patient::patient.navigation.label',
    ],
    'urls' => [
        Patient::class => 'patient.patients.view',        // route name (record param)
        // Patient::class => '/patients/{id}/chart',      // or a {id} pattern
        // Patient::class => fn ($record) => ...,         // or a Closure
    ],
],

// or on the model:
class Patient extends Model implements HasActivityTitle
{
    public function activityTitle(Activity $activity): ?string
    {
        return $activity->event === 'deleted'
            ? $this->getRawOriginal('full_name')
            : $this->full_name;
    }
}
```

### Subject links

When a URL resolves for the subject (config `urls` map first, then the Filament
resource's `view` page if the current panel has one), the subject title on the list
page renders as a link. Disable entirely with `subject.link.enabled = false`. Route
names with extra parameters should use the Closure form. The destination page enforces
its own access policy; the link is shown regardless.

### Timezone reinterpretation

Stored activity timestamps are wall-clock strings with no offset. If some rows were
written by an environment whose app timezone differed from the one you display in
(e.g. a production container without `APP_TIMEZONE` writing UTC), set:

```env
FB_ACTIVITY_STORAGE_TIMEZONE=UTC
FB_ACTIVITY_DISPLAY_TIMEZONE=Asia/Tehran
```

All rendering (list, view, export) shifts those stored walls into the display
timezone, and the created-at date filter converts your display-timezone bounds into
storage-timezone days. There is no data migration — the original offset was never
recorded, so existing rows can only be _reinterpreted_, not rewritten.

---

## Usage

### Register the plugin in a panel

```php
use Mortezamasumi\FbActivity\FbActivityPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(FbActivityPlugin::make());
}
```

### Human-readable subject names

```php
use Mortezamasumi\FbActivity\Facades\FbActivity;

FbActivity::getSubjectName($record, $subjectType); // 'Podcast' from 'App\Models\Podcast'
FbActivity::getSubject($record, $subjectType);     // 'Podcast ↣ Episode 42' when the subject resolves
```

`getSubject()` resolves the activity's subject model by ID and displays its `name`, `title`, or
`text` attribute (in that order); it falls back to the raw `subject_id` when the class is unknown.

### Permissions

Create the permissions with Filament Shield as `Activity` permissions and add the resource via
`filamentShieldAddResource` (done automatically by the service provider):

| Permission              | Effect                                                |
| ----------------------- | ----------------------------------------------------- |
| `ViewAny:Activity`      | view the resource index                               |
| `View:Activity`         | view a single activity                                |
| `Delete:Activity`       | bulk-delete activities                                |
| `Export:Activity`       | export activities as CSV                              |
| `ViewAllUsers:Activity` | see activities by all users (otherwise only your own) |

---

## Support policy

| PHP | Laravel | Filament |
| --- | ------- | -------- |
| 8.3 | 12      | 5.x      |

---

## Testing

```bash
composer test
```

---

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

If you discover a security vulnerability, please review our [security policy](.github/SECURITY.md) on how to report it.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

---

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
