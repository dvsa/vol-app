<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\HtmlToEditorJsConverter;
use Dvsa\Olcs\Api\Service\EditorJs\UnconvertibleContentException;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DeclarationPartialMigrationTest extends TestCase
{
    private const NEEDS_HAND_AUTHORING = [
        'markup-application_undertakings_GV79-NI.phtml',
        'markup-application_undertakings_GV81.phtml',
        'markup-application_undertakings_GV81-NI.phtml',
        'markup-application_undertakings_PSV430.phtml',
    ];

    private const NOT_EDITABLE_CONTENT = [
        'markup-application_undertakings_signature.phtml',
    ];

    private const PARTIAL_DIRS = [
        __DIR__ . '/../../../../../../module/Snapshot/config/language/partials',
        __DIR__ . '/../../../../../../../../lib/olcs-common/Common/config/language/partials',
    ];

    /**
     * @return array<string, array{string}>
     */
    public static function declarationPartialProvider(): array
    {
        $cases = [];

        foreach (self::PARTIAL_DIRS as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            foreach (glob($dir . '/*/markup-application_undertakings_*.phtml') ?: [] as $file) {
                $cases[basename(dirname($file)) . '/' . basename($file)] = [$file];
            }
        }

        return $cases;
    }

    #[DataProvider('declarationPartialProvider')]
    public function testPartialSurvivesTheRoundTrip(string $file): void
    {
        if (in_array(basename($file), self::NOT_EDITABLE_CONTENT, true)) {
            self::assertStringContainsString(
                '<form',
                (string) file_get_contents($file),
                'this is listed as page furniture but no longer looks like it',
            );

            return;
        }

        $original = file_get_contents($file);
        self::assertIsString($original);

        // Partials are plain markup plus %s substitution points; none of the
        // undertakings templates execute PHP.
        self::assertStringNotContainsString('<?php', $original, 'partial contains PHP and needs handling by hand');

        try {
            $blocks = (new HtmlToEditorJsConverter())->convert($original);
        } catch (UnconvertibleContentException $e) {
            // Recorded rather than silently passed: these need authoring by
            // hand, and testPartialsNeedingHandAuthoring pins how many there are.
            self::assertContains(basename($file), self::NEEDS_HAND_AUTHORING, $e->getMessage());

            return;
        }

        self::assertNotContains(
            basename($file),
            self::NEEDS_HAND_AUTHORING,
            'this partial now converts cleanly and should be removed from the list',
        );

        self::assertNotSame([], $blocks, 'nothing was converted');

        $rendered = (new LongTextConverterService())->convertJsonToHtml(
            json_encode(['time' => 0, 'version' => '2.28.2', 'blocks' => $blocks], JSON_THROW_ON_ERROR),
        );

        self::assertSame(
            self::textOf($original),
            self::textOf($rendered),
            'wording changed during migration',
        );
    }

    private static function textOf(string $html): string
    {

        $text = strip_tags(str_replace('>', '> ', $html));
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
