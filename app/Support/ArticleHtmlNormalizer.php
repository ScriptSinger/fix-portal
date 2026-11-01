<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Приводит HTML статьи к семантической структуре, не меняя текст и данные.
 * Визуальное оформление задаётся frontend, поэтому старые style/class удаляются.
 */
final class ArticleHtmlNormalizer
{
    private const ALLOWED = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote',
        'table', 'caption', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
        'a', 'img', 'figure', 'figcaption', 'iframe', 'pre', 'code', 'hr',
    ];

    public static function normalize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousState = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="article-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOBLANKS
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousState);

        $root = $document->getElementById('article-root');
        if (! $root) {
            return $html;
        }

        foreach (iterator_to_array($root->childNodes) as $child) {
            self::cleanNode($child);
        }

        self::removeLegacyWidgetText($root);
        self::removeEmbeddedToc($root);

        foreach (iterator_to_array($root->getElementsByTagName('table')) as $table) {
            self::normalizeTable($table);
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    private static function removeLegacyWidgetText(DOMElement $root): void
    {
        $xpath = new \DOMXPath($root->ownerDocument);
        foreach ($xpath->query('.//text()[contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "vk.widgets") or contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "vk_post") or contains(translate(., "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "vk.com/js/api")]') as $text) {
            $text->parentNode?->removeChild($text);
        }

        foreach (iterator_to_array($root->getElementsByTagName('p')) as $paragraph) {
            if (trim($paragraph->textContent) === '' && $paragraph->getElementsByTagName('img')->length === 0) {
                $paragraph->parentNode?->removeChild($paragraph);
            }
        }
    }

    private static function removeEmbeddedToc(DOMElement $root): void
    {
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $headingTag) {
            foreach (iterator_to_array($root->getElementsByTagName($headingTag)) as $heading) {
                $title = trim(preg_replace('/\s+/u', ' ', $heading->textContent) ?? '');
                if (! in_array(mb_strtolower($title), ['содержание', 'оглавление'], true)) {
                    continue;
                }

                $next = $heading->nextSibling;
                while ($next && $next->nodeType === XML_TEXT_NODE && trim($next->textContent) === '') {
                    $next = $next->nextSibling;
                }

                if (! $next instanceof DOMElement || ! in_array(strtolower($next->tagName), ['ol', 'ul'], true)) {
                    continue;
                }

                $links = $next->getElementsByTagName('a');
                if ($links->length < 2) {
                    continue;
                }

                $heading->parentNode?->removeChild($heading);
                $next->parentNode?->removeChild($next);
            }
        }
    }

    private static function cleanNode(DOMNode $node): void
    {
        if ($node instanceof DOMText) {
            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, ['script', 'style', 'object', 'embed'], true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if (! in_array($tag, self::ALLOWED, true)) {
            $parent = $node->parentNode;
            if (! $parent) {
                return;
            }

            $children = iterator_to_array($node->childNodes);
            foreach ($children as $child) {
                $parent->insertBefore($child, $node);
                self::cleanNode($child);
            }
            $parent->removeChild($node);
            return;
        }

        self::cleanAttributes($node);

        foreach (iterator_to_array($node->childNodes) as $child) {
            self::cleanNode($child);
        }
    }

    private static function cleanAttributes(DOMElement $node): void
    {
        $allowed = match (strtolower($node->tagName)) {
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'width', 'height', 'data-full'],
            'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'title', 'loading'],
            'th', 'td' => ['colspan', 'rowspan', 'scope'],
            default => [],
        };

        foreach (iterator_to_array($node->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), $allowed, true)) {
                $node->removeAttributeNode($attribute);
            }
        }

        if (strtolower($node->tagName) === 'a') {
            $href = trim($node->getAttribute('href'));
            if ($href === '' || ! preg_match('~^(https?:|mailto:|tel:|/|#)~i', $href)) {
                $node->removeAttribute('href');
            }
        }

        if (strtolower($node->tagName) === 'iframe') {
            $src = trim($node->getAttribute('src'));
            if ($src === '' || ! preg_match('~^(https?:|/)~i', $src)) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    private static function normalizeTable(DOMElement $table): void
    {
        foreach (iterator_to_array($table->getElementsByTagName('tbody')) as $tbody) {
            $hasRows = false;
            foreach ($tbody->childNodes as $child) {
                if ($child instanceof DOMElement && strtolower($child->tagName) === 'tr') {
                    $hasRows = true;
                    break;
                }
            }
            if (! $hasRows) {
                $tbody->parentNode?->removeChild($tbody);
            }
        }

        $caption = null;
        foreach ($table->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'caption') {
                $caption = $child;
                break;
            }
        }
        if ($caption && $table->firstChild !== $caption) {
            $table->insertBefore($caption, $table->firstChild);
        }

        foreach (iterator_to_array($table->getElementsByTagName('tr')) as $row) {
            $cells = [];
            $elementChildren = [];
            foreach ($row->childNodes as $child) {
                if (! $child instanceof DOMElement) {
                    continue;
                }

                $elementChildren[] = $child;
                if (strtolower($child->tagName) === 'td') {
                    $cells[] = $child;
                }
            }

            if ($cells === [] || count($cells) !== count($elementChildren)) {
                continue;
            }

            foreach ($cells as $cell) {
                $header = $row->ownerDocument->createElement('th');
                $header->setAttribute('scope', 'col');
                while ($cell->firstChild) {
                    $header->appendChild($cell->firstChild);
                }
                foreach (['colspan', 'rowspan'] as $attribute) {
                    if ($cell->hasAttribute($attribute)) {
                        $header->setAttribute($attribute, $cell->getAttribute($attribute));
                    }
                }
                $row->replaceChild($header, $cell);
            }
            break;
        }
    }
}
