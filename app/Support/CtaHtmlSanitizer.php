<?php

namespace App\Support;

use DOMDocument;

/**
 * Чистит HTML текста CTA: оставляет только безопасные теги и ссылки.
 */
class CtaHtmlSanitizer
{
    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return null;
        }

        $html = preg_replace('#<(script|style|iframe|object|embed)[^>]*>.*?</\1>#is', '', $html) ?? '';
        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><a>');

        if ($html === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);
        $document->loadHTML(
            mb_convert_encoding('<div>' . $html . '</div>', 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        foreach ($document->getElementsByTagName('*') as $node) {
            if ($node->nodeName !== 'a') {
                while ($node->attributes->length > 0) {
                    $node->removeAttribute($node->attributes->item(0)->nodeName);
                }
                continue;
            }

            $href = trim((string) $node->getAttribute('href'));
            $isSafeHref = $href !== '' && preg_match('#^(https?:|mailto:|tel:|/|#)#i', $href);

            while ($node->attributes->length > 0) {
                $node->removeAttribute($node->attributes->item(0)->nodeName);
            }

            if ($isSafeHref) {
                $node->setAttribute('href', $href);
            }
        }

        $sanitized = trim($document->saveHTML($document->documentElement) ?: '');

        return $sanitized !== '' ? $sanitized : null;
    }
}
