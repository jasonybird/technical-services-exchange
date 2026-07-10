<?php

return [
    'attachments' => [
        'disk' => env('TSE_ATTACHMENT_DISK', 'public'),
        'root' => trim(env('TSE_ATTACHMENT_ROOT', 'attachments'), '/'),
        'max_kb' => (int) env('TSE_ATTACHMENT_MAX_KB', 10240),
        'allowed_mime_types' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('TSE_ATTACHMENT_MIME_TYPES', 'image/jpeg,image/png,image/webp,application/pdf,text/plain,text/csv,application/zip'))
        ))),
    ],
];
