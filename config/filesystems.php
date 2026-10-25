<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    // Диск для загрузок из админки (картинки TinyMCE и т.п.)
    'media' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Приватный бакет Yandex Object Storage для файлов прошивок.
        // Скачивание только по временной подписанной ссылке (контроллер проверяет права).
        'firmware_s3' => [
            'driver' => 's3',
            'key' => env('YANDEX_S3_KEY'),
            'secret' => env('YANDEX_S3_SECRET'),
            'region' => env('YANDEX_S3_REGION', 'ru-central1'),
            'bucket' => env('YANDEX_FIRMWARE_BUCKET'),
            'endpoint' => env('YANDEX_S3_ENDPOINT', 'https://storage.yandexcloud.net'),
            'use_path_style_endpoint' => env('YANDEX_S3_PATH_STYLE', true),
            'visibility' => 'private',
            'throw' => true,
        ],

        // Приватный бакет для дампов базы (php artisan db:backup). В дампе e-mail и хеши паролей
        // пользователей, поэтому бакет отдельный от бакета с прошивками.
        // Отдельный ключ можно завести через BACKUP_S3_KEY, иначе берётся общий ключ Yandex.
        'backups' => [
            'driver' => 's3',
            'key' => env('BACKUP_S3_KEY', env('YANDEX_S3_KEY')),
            'secret' => env('BACKUP_S3_SECRET', env('YANDEX_S3_SECRET')),
            'region' => env('BACKUP_S3_REGION', env('YANDEX_S3_REGION', 'ru-central1')),
            // YANDEX_BACKUP_BUCKET в одном ряду с YANDEX_FIRMWARE_BUCKET; BACKUP_S3_BUCKET оставлен для совместимости
            'bucket' => env('YANDEX_BACKUP_BUCKET', env('BACKUP_S3_BUCKET')),
            'endpoint' => env('BACKUP_S3_ENDPOINT', env('YANDEX_S3_ENDPOINT', 'https://storage.yandexcloud.net')),
            'use_path_style_endpoint' => env('YANDEX_S3_PATH_STYLE', true),
            'visibility' => 'private',
            'throw' => true,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
