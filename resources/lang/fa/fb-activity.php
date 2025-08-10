<?php

return [
    'export'     => [
        'label'   => 'برون‌برد فعالیت‌ها',
        'heading' => 'برون‌برد فعالیت‌ها',
        'action'  => 'برون‌برد',
    ],
    'columns'    => [
        'type'        => 'نوع',
        'event'       => 'رویداد',
        'subject'     => 'موضوع',
        'subject_id'  => 'آی دی موضوع',
        'causer'      => 'عامل',
        'causer_id'   => 'آی دی عامل',
        'properties'  => 'مقادیر',
        'description' => 'توضیح',
        'created_at'  => 'زمان ایجاد',
    ],
    'navigation' => [
        'label'      => 'فعالیت‌ها',
        'system'     => 'سامانه',
        'activity'   => 'فعالیت',
        'activities' => 'فعالیت‌ها',
    ],
    'form'       => [
        'filter' => [
            'created_from'  => 'شروع از',
            'created_until' => 'خاتمه تا',
        ],
    ],
    'view'       => [
        'old'     => 'مقادیر قبلی',
        'current' => 'مقادیر فعلی',
    ],
    'date'       => [
        'format' => 'H:i Y/m/d',
    ],
];
