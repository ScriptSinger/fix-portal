<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use DOMDocument;
use Illuminate\Http\Request;

class PostCtaController extends Controller
{
    public function upsert(Request $request, Post $post)
    {
        $data = $request->validate([
            'cta_target_url' => 'required|url|max:2048',
            'cta_city' => 'nullable|string|max:64',
            'cta_brand' => 'nullable|string|max:64',
            'cta_appliance_type' => 'nullable|string|max:64',
            'cta_problem' => 'nullable|string|max:64',
            'cta_title' => 'required|string|max:255',
            'cta_text' => 'nullable|string',
            'cta_anchor' => 'required|string|max:255',
            'cta_placement' => 'required|string|in:middle,end,sidebar',
            'cta_priority' => 'nullable|integer|min:0',
            'cta_is_active' => 'nullable|boolean',
        ], [
            'cta_target_url.required' => 'Укажите URL посадочной.',
            'cta_title.required' => 'Укажите заголовок CTA.',
            'cta_anchor.required' => 'Укажите текст ссылки CTA.',
        ]);

        $post->ctas()->updateOrCreate(
            [
                'placement' => $data['cta_placement'],
            ],
            [
                'target_url' => $data['cta_target_url'],
                'city' => $data['cta_city'] ?? null,
                'brand' => $data['cta_brand'] ?? null,
                'appliance_type' => $data['cta_appliance_type'] ?? null,
                'problem' => $data['cta_problem'] ?? null,
                'title' => $data['cta_title'],
                'text' => $this->sanitizeCtaHtml($data['cta_text'] ?? null),
                'anchor' => $data['cta_anchor'],
                'priority' => $data['cta_priority'] ?? 0,
                'is_active' => (bool) ($data['cta_is_active'] ?? false),
            ]
        );

        return redirect()
            ->route('admin.posts.edit', ['post' => $post->id])
            ->with('success', 'CTA сохранен');
    }

    public function destroy(Post $post)
    {
        $placement = request('placement', 'end');

        $post->ctas()->where('placement', $placement)->delete();

        return redirect()
            ->route('admin.posts.edit', ['post' => $post->id])
            ->with('success', 'CTA удален');
    }

    private function sanitizeCtaHtml(?string $html): ?string
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
