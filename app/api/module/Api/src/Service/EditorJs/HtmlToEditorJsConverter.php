<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

final class HtmlToEditorJsConverter
{
    private const HEADING_TAGS = ['h1' => 1, 'h2' => 2, 'h3' => 3, 'h4' => 4, 'h5' => 5, 'h6' => 6];

    /** Fragments substituted into a parent template are often bare inline markup — the "-declare" partials are a lone */
    private const INLINE_TAGS = ['strong', 'b', 'em', 'i', 'a', 'span', 'u', 'br', 'abbr'];

    /**
     * @return list<array{id: string, type: string, data: array}>
     */
    public function convert(string $html): array
    {
        $document = $this->parse($html);
        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return [];
        }

        return $this->blocksFrom($body);
    }

    /**
     * @return list<array{id: string, type: string, data: array}>
     */
    private function blocksFrom(DOMNode $parent): array
    {
        $blocks = [];
        $inline = '';
        $listItems = [];

        $flushInline = function () use (&$inline, &$blocks): void {
            $text = $this->normalise($inline);
            $inline = '';

            if ($text !== '') {
                $blocks[] = $this->block('paragraph', ['text' => $text]);
            }
        };

        $flushItems = function () use (&$listItems, &$blocks): void {
            if ($listItems !== []) {
                $blocks[] = $this->block('list', ['style' => 'bullet', 'items' => $listItems]);
                $listItems = [];
            }
        };

        foreach ($parent->childNodes as $node) {
            $tag = $node instanceof DOMElement ? strtolower($node->nodeName) : null;

            // Loose <li> fragments are list items meant to be substituted into a
            // parent list; consecutive ones become a single list.
            if ($tag === 'li') {
                $flushInline();
                $listItems[] = $this->normalise($this->innerHtml($node));
                continue;
            }

            $flushItems();

            if ($node instanceof DOMText || ($tag !== null && in_array($tag, self::INLINE_TAGS, true))) {
                $inline .= $node instanceof DOMText
                    ? $node->textContent
                    : ($node->ownerDocument?->saveHTML($node) ?? '');
                continue;
            }

            $flushInline();

            // Containers carry no meaning of their own here.
            if ($tag === 'div') {
                $blocks = array_merge($blocks, $this->blocksFrom($node));
                continue;
            }

            $block = $this->toBlock($node);

            if ($block !== null) {
                $blocks[] = $block;
            }
        }

        $flushInline();
        $flushItems();

        return $blocks;
    }

    /**
     * @return array{id: string, type: string, data: array}|null
     */
    private function toBlock(DOMNode $node): ?array
    {
        if (!$node instanceof DOMElement) {
            return null;
        }

        $tag = strtolower($node->nodeName);

        if ($tag === 'p') {
            $text = $this->normalise($this->innerHtml($node));

            return $text === '' ? null : $this->block('paragraph', ['text' => $text]);
        }

        if (isset(self::HEADING_TAGS[$tag])) {
            return $this->block('heading', [
                'text' => $this->normalise($this->innerHtml($node)),
                'level' => self::HEADING_TAGS[$tag],
            ]);
        }

        if ($tag === 'ul' || $tag === 'ol') {
            return $this->block('list', [
                'style' => $tag === 'ol' ? 'number' : 'bullet',
                'items' => $this->listItems($node),
            ]);
        }

        return null;
    }

    /**
     * @return list<string>
     *
     * @throws UnconvertibleContentException if the list contains a substitution
     *         point, which the block model cannot hold — items are flat strings,
     *         so a %s between them has nowhere to live and would be dropped
     */
    private function listItems(DOMElement $list): array
    {
        $items = [];

        foreach ($list->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->nodeName) === 'li') {
                $items[] = $this->normalise($this->innerHtml($child));
                continue;
            }

            if ($child instanceof DOMText && str_contains($child->textContent, '%s')) {
                throw new UnconvertibleContentException(
                    'A substitution placeholder sits between list items. The list would have to be '
                    . 'split, or the surrounding code changed to compose from separate records, '
                    . 'so this content needs authoring by hand rather than converting.',
                );
            }
        }

        return $items;
    }

    private function block(string $type, array $data): array
    {
        return ['id' => substr(bin2hex(random_bytes(8)), 0, 10), 'type' => $type, 'data' => $data];
    }

    private function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    /** Templates are indented for readability, which would otherwise become runs of whitespace in the stored content. */
    private function normalise(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    private function parse(string $html): DOMDocument
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR | LIBXML_NOWARNING,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        // NOIMPLIED omits html/body, so wrap for a predictable root.
        if ($document->getElementsByTagName('body')->length === 0) {
            $wrapper = new DOMDocument();
            libxml_use_internal_errors(true);
            $wrapper->loadHTML(
                '<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>',
                LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
            );
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return $wrapper;
        }

        return $document;
    }
}
