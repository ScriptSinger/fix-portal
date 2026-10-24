<?php

declare(strict_types=1);

namespace App\MoonShine\Support;

use MoonShine\TinyMce\Fields\TinyMce;

final class TinyMceUpload
{
    /**
     * Wires a TinyMCE field's inline image uploads to the app's
     * `moonshine.tinymce.upload` endpoint (stores on `filesystems.media`, e.g. Yandex S3).
     */
    public static function withImageUpload(TinyMce $field): TinyMce
    {
        $uploadUrl = route('moonshine.tinymce.upload');

        $uploadHandler = <<<JS
            (blobInfo, progress) => new Promise((resolve, reject) => {
                const url = "{$uploadUrl}";
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());

                fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
                    .then(({ ok, data }) => {
                        if (!ok) {
                            reject(data?.message || 'Upload failed');
                            return;
                        }

                        if (data && typeof data.location === 'string') {
                            resolve(data.location);
                            return;
                        }

                        reject('Invalid upload response');
                    })
                    .catch(() => reject('Upload failed'));
            })
        JS;

        return $field
            ->addOption('automatic_uploads', true)
            ->addCallback('images_upload_handler', $uploadHandler)
            ->addOption('forced_root_block', 'p')
            ->addOption('force_p_newlines', true)
            ->addOption('force_br_newlines', false)
            ->addOption('convert_newlines_to_brs', false);
    }
}
