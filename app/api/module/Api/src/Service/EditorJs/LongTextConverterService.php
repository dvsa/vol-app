<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer\GovukHeaderBlockRenderer;
use Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer\GovukListBlockRenderer;
use Dvsa\Olcs\Api\Service\EditorJs\BlockRenderer\GovukParagraphBlockRenderer;
use Setono\EditorJS\Parser\Parser;
use Setono\EditorJS\Renderer\Renderer;

/** Converts Long Text EditorJS JSON into GOV.UK Frontend markup. */
final class LongTextConverterService
{
    private const LIST_STYLES = [
        'bullet' => 'unordered',
        'number' => 'ordered',
    ];

    private readonly Parser $parser;

    private readonly Renderer $renderer;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->renderer = new Renderer();
        $this->renderer->add(new GovukHeaderBlockRenderer());
        $this->renderer->add(new GovukParagraphBlockRenderer());
        $this->renderer->add(new GovukListBlockRenderer());
    }

    public function convertJsonToHtml(string $jsonData): string
    {
        if ($jsonData === '') {
            return '';
        }

        return $this->sanitise(
            $this->renderer->render($this->parser->parse($this->adaptForParser($jsonData))),
        );
    }

    /**
     * @throws \RuntimeException if HTMLPurifier is unavailable
     */
    private function sanitise(string $html): string
    {
        if (!class_exists(\HTMLPurifier::class)) {
            throw new \RuntimeException('HTMLPurifier is required for secure HTML sanitization');
        }

        $config = \HTMLPurifier_Config::createDefault();

        // HTMLPurifier's default definition cache lives inside the vendor tree,
        // which is read-only in deployed containers.
        $config->set('Cache.SerializerPath', sys_get_temp_dir());

        // The URI scheme registry is a process-global singleton.
        $config->set('URI.OverrideAllowedSchemes', false);

        $clean = (new \HTMLPurifier($config))->purify($html);

        // An empty block renders as a blank gap; the letter converter drops
        // these too.
        $clean = (string) preg_replace('/<p[^>]*>\s*<\/p>/', '', $clean);

        return trim($clean);
    }

    /** The GOV.UK tools name blocks differently from the Setono parser. */
    private function adaptForParser(string $jsonData): string
    {
        $data = json_decode($jsonData, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data) || !isset($data['blocks']) || !is_array($data['blocks'])) {
            return $jsonData;
        }

        // Only documents saved through the editor carry the time/version envelope and per-block ids
        $data['time'] ??= 0;
        $data['version'] ??= '2.28.2';

        foreach ($data['blocks'] as $index => $block) {
            if (!is_array($block)) {
                continue;
            }

            $data['blocks'][$index]['id'] ??= substr(md5((string) $index . serialize($block)), 0, 10);

            if (($block['type'] ?? null) === 'heading') {
                $data['blocks'][$index]['type'] = 'header';
            }

            if (($block['type'] ?? null) === 'list' && is_array($block['data'] ?? null)) {
                $style = $block['data']['style'] ?? null;

                if (isset(self::LIST_STYLES[$style])) {
                    $data['blocks'][$index]['data']['style'] = self::LIST_STYLES[$style];
                }
            }
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
