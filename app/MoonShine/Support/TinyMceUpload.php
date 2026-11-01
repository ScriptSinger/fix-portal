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
            ->addOption('language', 'ru')
            ->addOption('language_url', asset('vendor/moonshine-tinymce/langs/ru.js'))
            // Редактор отвечает за семантическую структуру, а не за визуальные стили страницы.
            ->addOption('menubar', false)
            ->addOption('plugins', 'lists link image table')
            ->addOption('toolbar', 'undo redo | blocks | bold italic | bullist numlist | blockquote | link image table')
            ->addOption('block_formats', 'Абзац=p;Заголовок 2=h2;Заголовок 3=h3;Заголовок 4=h4;Цитата=blockquote;Код=pre')
            ->addOption('style_formats', [])
            ->addOption('paste_webkit_styles', 'none')
            ->addOption('paste_remove_styles_if_webkit', true)
            ->addOption('valid_elements', 'p,br,strong/b,em/i,u,s,h2,h3,h4,h5,h6,ul,ol,li,blockquote,table,caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan],a[href|title|target],img[src|alt|width|height],figure,figcaption,pre,code,hr')
            ->addOption('automatic_uploads', true)
            ->addCallback('images_upload_handler', $uploadHandler)
            ->addOption('forced_root_block', 'p')
            ->addOption('force_p_newlines', true)
            ->addOption('force_br_newlines', false)
            ->addOption('convert_newlines_to_brs', false);
    }
}
