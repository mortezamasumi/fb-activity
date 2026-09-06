<?php

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
    // Stored `created_at` values are wall-clock strings with no offset. If they
    // were written in a different timezone than the app (e.g. UTC rows imported
    // from an environment without APP_TIMEZONE), set `storage` to that zone and
    // they are reinterpreted and shifted for display to `display` (or the app tz).
    // Both null = default Laravel behavior (no reinterpretation).
    'timezone' => [
        'storage' => env('FB_ACTIVITY_STORAGE_TIMEZONE'),
        'display' => env('FB_ACTIVITY_DISPLAY_TIMEZONE'),
    ],
    'subject' => [
        // Model FQCN => attribute/dot-path | Closure | invokable class-string | null.
        'titles' => [],
        // Model FQCN => label string or translation key.
        'labels' => [],
        // Model FQCN => route name | pattern with {id} | Closure | invokable class-string.
        'urls' => [],
        'attribute_cascade' => ['display_name', 'full_name', 'name', 'title'],
        'use_filament_record_title' => true,
        'show_model_label' => true,
        'recover_deleted' => true,
        'link' => [
            'enabled' => true,
        ],
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
