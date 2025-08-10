<?php

return [
    'export' => [
        'label' => 'Export activities',
        'heading' => 'Export activities',
        'action' => 'Export',
    ],
    'columns' => [
        'type' => 'Type',
        'event' => 'Event',
        'subject' => 'Subject',
        'subject_id' => 'Subject Id',
        'causer' => 'Causer',
        'causer_id' => 'Causer Id',
        'properties' => 'Properties',
        'description' => 'Description',
        'created_at' => 'Created At',
    ],
    'navigation' => [
        'label' => 'Activities',
        'system' => 'System',
        'activity' => 'Activity',
        'activities' => 'Activities',
    ],
    'form' => [
        'filter' => [
            'created_from' => 'Created from',
            'created_until' => 'Created until',
        ],
    ],
    'view' => [
        'old' => 'Old attributes',
        'current' => 'Current attributes',
    ],
    'date' => [
        'format' => 'Y/m/d H:i',
    ],
];
