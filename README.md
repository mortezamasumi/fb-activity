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
];
```

- `navigation.*` — panel navigation label, group, icons, badge, and sort order
- `export.max_export_rows` — maximum rows per export (default `3000`)
- `exclude_logs` / `include_logs` — comma-separated description patterns; `*` matches any characters and `?` matches a single character. Include patterns apply as `OR` (any match is kept), exclude patterns apply as `NOT LIKE` on top of the include filter

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

| Permission | Effect |
| --- | --- |
| `ViewAny:Activity` | view the resource index |
| `View:Activity` | view a single activity |
| `Delete:Activity` | bulk-delete activities |
| `Export:Activity` | export activities as CSV |
| `ViewAllUsers:Activity` | see activities by all users (otherwise only your own) |

---

## Support policy

| PHP | Laravel | Filament |
| --- | --- | --- |
| 8.3 | 12 | 5.x |

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
