<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LongTextConverterService::class)]
final class LongTextConverterServiceTest extends TestCase
{
    public function testHeadingRendersWithMatchingGovukHeadingClass(): void
    {
        $json = json_encode([
            'time' => 1234567890,
            'version' => '2.28.2',
            'blocks' => [
                [
                    'id' => 'heading-1',
                    'type' => 'heading',
                    'data' => ['text' => 'Declaration by the applicant', 'level' => 3],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $html = (new LongTextConverterService())->convertJsonToHtml($json);

        self::assertSame(
            '<h3 class="govuk-heading-m">Declaration by the applicant</h3>',
            $html,
        );
    }

    #[DataProvider('headingLevelProvider')]
    public function testHeadingLevelMapsToItsGovukClass(int $level, string $expected): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'heading-1',
                'type' => 'heading',
                'data' => ['text' => 'Heading', 'level' => $level],
            ]),
        );

        self::assertSame($expected, $html);
    }

    public static function headingLevelProvider(): array
    {
        return [
            'level 1' => [1, '<h1 class="govuk-heading-xl">Heading</h1>'],
            'level 2' => [2, '<h2 class="govuk-heading-l">Heading</h2>'],
            'level 3' => [3, '<h3 class="govuk-heading-m">Heading</h3>'],
            'level 4' => [4, '<h4 class="govuk-heading-s">Heading</h4>'],
            'unmapped level falls back' => [6, '<h6 class="govuk-heading-l">Heading</h6>'],
        ];
    }

    public function testParagraphRendersWithGovukBodyClass(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'paragraph-1',
                'type' => 'paragraph',
                'data' => ['text' => 'I confirm that the details given are correct.'],
            ]),
        );

        self::assertSame(
            '<p class="govuk-body">I confirm that the details given are correct.</p>',
            $html,
        );
    }

    #[DataProvider('listStyleProvider')]
    public function testListRendersWithGovukListClasses(string $style, string $expected): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'list-1',
                'type' => 'list',
                'data' => ['style' => $style, 'items' => ['Vehicles kept at the centre', 'Records kept 15 months']],
            ]),
        );

        self::assertSame($expected, $html);
    }

    public static function listStyleProvider(): array
    {
        return [
            'bulleted' => [
                'bullet',
                '<ul class="govuk-list govuk-list--bullet">'
                . '<li>Vehicles kept at the centre</li><li>Records kept 15 months</li></ul>',
            ],
            'numbered' => [
                'number',
                '<ol class="govuk-list govuk-list--number">'
                . '<li>Vehicles kept at the centre</li><li>Records kept 15 months</li></ol>',
            ],
        ];
    }

    public function testScriptContentIsRemoved(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'paragraph-1',
                'type' => 'paragraph',
                'data' => ['text' => 'You must sign this declaration.<script>alert(1)</script>'],
            ]),
        );

        self::assertSame('<p class="govuk-body">You must sign this declaration.</p>', $html);
    }

    public function testEventHandlerAttributesAreRemoved(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'paragraph-1',
                'type' => 'paragraph',
                'data' => ['text' => '<b onclick="steal()">Important</b>'],
            ]),
        );

        self::assertSame('<p class="govuk-body"><b>Important</b></p>', $html);
    }

    public function testNestedListMarkupInsideAnItemSurvives(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document([
                'id' => 'list-1',
                'type' => 'list',
                'data' => [
                    'style' => 'bullet',
                    'items' => [
                        'Events which affect:<ul class="govuk-list govuk-list--bullet">'
                        . '<li>The good repute of the licence holder</li></ul>',
                    ],
                ],
            ]),
        );

        self::assertStringContainsString('<li>The good repute of the licence holder</li>', $html);
        self::assertStringContainsString('govuk-list--bullet', $html);
    }

    public function testContentWithoutTheEditorEnvelopeStillRenders(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            json_encode([
                'blocks' => [['type' => 'paragraph', 'data' => ['text' => 'I declare that the details are correct.']]],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertSame(
            '<p class="govuk-body">I declare that the details are correct.</p>',
            $html,
        );
    }

    public function testEmptyParagraphsAreDropped(): void
    {
        $html = (new LongTextConverterService())->convertJsonToHtml(
            self::document(
                ['id' => 'a', 'type' => 'paragraph', 'data' => ['text' => 'Kept.']],
                ['id' => 'b', 'type' => 'paragraph', 'data' => ['text' => '  ']],
            ),
        );

        self::assertSame('<p class="govuk-body">Kept.</p>', $html);
    }

    private static function document(array ...$blocks): string
    {
        return json_encode([
            'time' => 1234567890,
            'version' => '2.28.2',
            'blocks' => $blocks,
        ], JSON_THROW_ON_ERROR);
    }
}
