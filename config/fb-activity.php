<?php

return [
    'navigation' => [
        'model_label' => 'fb-activity::fb-activity.navigation.label',
        'plural_model_label' => 'fb-activity::fb-activity.navigation.plural_label',
        'group' => 'fb-activity::fb-activity.navigation.group',
        'parent_item' => null,
        'icon' => 'heroicon-o-queue-list',
        'active_icon' => null,
        'badge' => false,
        'badge_tooltip' => null,
        'sort' => 9900,
    ],
    'export' => [
        'exporter' => '\Mortezamasumi\FbActivity\Exports\FbActivityExporter',
        'max_export_rows' => env('ACTIVITY_MAX_EXPORT_ROWS', 3000),
    ]
];
