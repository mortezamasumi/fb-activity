<?php

return [
    'navigation' => [
        'icon' => 'heroicon-o-queue-list',
        'sort' => 9900,
        'label' => 'fb-activity::fb-activity.navigation.label',
        'group' => 'fb-activity::fb-activity.navigation.system',
        'model_label' => 'fb-activity::fb-activity.navigation.activity',
        'plural_model_label' => 'fb-activity::fb-activity.navigation.activities',
        'show_count' => false,
        'parent_item' => null,
        'active_icon' => null,
    ],
    'export' => [
        'exporter' => '\Mortezamasumi\FbActivity\Exports\FbActivityExporter',
        'max_export_rows' => env('ACTIVITY_MAX_EXPORT_ROWS', 3000),
    ]
];
