<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\HtmlToEditorJsConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlToEditorJsConverter::class)]
final class HtmlToEditorJsConverterTest extends TestCase
{
    private HtmlToEditorJsConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new HtmlToEditorJsConverter();
    }

    public function testItConvertsParagraphs(): void
    {
        $blocks = $this->sut->convert('<p class="govuk-body">I declare that the statements are correct.</p>');

        self::assertSame('paragraph', $blocks[0]['type']);
        self::assertSame('I declare that the statements are correct.', $blocks[0]['data']['text']);
    }

    public function testItConvertsBulletedLists(): void
    {
        $blocks = $this->sut->convert(
            '<ul class="govuk-list govuk-list--bullet"><li>First item</li><li>Second item</li></ul>',
        );

        self::assertSame('list', $blocks[0]['type']);
        self::assertSame('bullet', $blocks[0]['data']['style']);
        self::assertSame(['First item', 'Second item'], $blocks[0]['data']['items']);
    }

    public function testItConvertsNumberedLists(): void
    {
        $blocks = $this->sut->convert('<ol class="govuk-list"><li>Only item</li></ol>');

        self::assertSame('number', $blocks[0]['data']['style']);
    }

    public function testItConvertsHeadingsToTheirLevel(): void
    {
        $blocks = $this->sut->convert('<h3 class="govuk-heading-m">Declaration by the applicant</h3>');

        self::assertSame('heading', $blocks[0]['type']);
        self::assertSame(3, $blocks[0]['data']['level']);
        self::assertSame('Declaration by the applicant', $blocks[0]['data']['text']);
    }

    public function testItKeepsInlineMarkup(): void
    {
        $blocks = $this->sut->convert('<p>Signed by <b>a director</b> — see <a href="/x">the guidance</a>.</p>');

        self::assertSame(
            'Signed by <b>a director</b> — see <a href="/x">the guidance</a>.',
            $blocks[0]['data']['text'],
        );
    }

    public function testItKeepsNestedListsInsideTheItem(): void
    {
        $blocks = $this->sut->convert(
            '<ul><li>Events which affect:<ul><li>The good repute of the holder</li></ul></li></ul>',
        );

        self::assertCount(1, $blocks);
        self::assertStringContainsString('<ul><li>The good repute of the holder</li></ul>', $blocks[0]['data']['items'][0]);
    }

    public function testItPreservesSubstitutionPlaceholders(): void
    {
        $blocks = $this->sut->convert('<p>Ends here.</p>%s');

        self::assertSame('%s', $blocks[1]['data']['text']);
    }

    public function testItCollapsesTheWhitespaceTemplatesAreIndentedWith(): void
    {
        $blocks = $this->sut->convert("<p>\n  Wrapped over\n  several lines.\n</p>");

        self::assertSame('Wrapped over several lines.', $blocks[0]['data']['text']);
    }

    public function testEveryBlockCarriesAnId(): void
    {
        $blocks = $this->sut->convert('<p>One</p><p>Two</p>');

        self::assertNotSame('', $blocks[0]['id']);
        self::assertNotSame($blocks[0]['id'], $blocks[1]['id']);
    }
}
