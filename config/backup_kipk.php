<?php

return [
    'destination' => env('KIPK_BACKUP_PATH') ?: storage_path('app/backups'),
    'password' => env('KIPK_BACKUP_PASSWORD'),
    'mysqldump_binary' => env('KIPK_MYSQLDUMP_BINARY', 'mysqldump'),
];
