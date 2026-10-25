<?php

// Резервные копии базы (php artisan db:backup, расписание в app/Console/Kernel.php).
return [
    // Папка с дампами *.sql.gz на сервере. Локально держим только последние
    // дни: основное хранилище копий в облаке.
    'path' => env('BACKUP_PATH', storage_path('app/private/backups')),

    // Сколько дней хранить копии на сервере.
    'keep_days' => (int) env('BACKUP_KEEP_DAYS', 7),

    // Диск приватного бакета в Yandex Object Storage (config/filesystems.php)
    // и папка в нём. Копия вне сервера спасает, если откажет его диск.
    'cloud_disk' => 'backups',
    'cloud_prefix' => 'db',

    // Сколько дней хранить копии в облаке.
    'cloud_keep_days' => (int) env('BACKUP_CLOUD_KEEP_DAYS', 30),
];
