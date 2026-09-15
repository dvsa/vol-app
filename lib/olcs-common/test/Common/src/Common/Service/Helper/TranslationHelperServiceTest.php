<?php

/**
 * Translation Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Helper;

use Common\Service\Helper\TranslationHelperService;
use Laminas\I18n\Translator\Translator;

/**
 * Translation Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class TranslationHelperServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Holds the SUT
     *
     * @var \Common\Service\Helper\TranslationHelperService
     */
    private $sut;

    private $mockTranslator;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = $this->createPartialMock(Translator::class, ['translate']);
        $this->mockTranslator
            ->method('translate')
            ->willReturnCallback(fn($message, $domain, $locale) => $this->translate($message, $domain, $locale));

        $this->sut = new TranslationHelperService($this->mockTranslator);
    }

    /**
     * Mock translate method
     */
    public function translate($message, $domain, $locale): string
    {
        $translation = '';
        if ($locale === 'cy_GB') {
            $translation .= 'WELSH';
        }
        return $translation . ('*' . $message . '*');
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('translation_helper_service')]
    public function testGetTranslator(): void
    {
        $this->assertSame($this->mockTranslator, $this->sut->getTranslator());
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('translation_helper_service')]
    public function testTranslate(): void
    {
        $this->assertEquals('*foo*', $this->sut->translate('foo'));
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('translation_helper_service')]
    public function testWrapTranslation(): void
    {
        $format = 'This is a wrapped <div>%s</div>';
        $translation = 'translation';
        $expected = 'This is a wrapped <div>*translation*</div>';

        $this->assertEquals($expected, $this->sut->wrapTranslation($format, $translation));
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('translation_helper_service')]
    public function testFormatTranslation(): void
    {
        $format = 'This is a formatted <div>%s</div> message to %s multiple %s';
        $translations = [
            'translation',
            'demonstrate',
            'replacements'
        ];
        $expected = 'This is a formatted <div>*translation*</div> message to *demonstrate* multiple *replacements*';

        $this->assertEquals($expected, $this->sut->formatTranslation($format, $translations));
    }

    #[\PHPUnit\Framework\Attributes\Group('helper_service')]
    #[\PHPUnit\Framework\Attributes\Group('translation_helper_service')]
    public function testFormatTranslationWithSingleMessage(): void
    {
        $format = 'This is a formatted <div>%s</div>';
        $translations = 'translation';
        $expected = 'This is a formatted <div>*translation*</div>';

        $this->assertEquals($expected, $this->sut->formatTranslation($format, $translations));
    }

    public function testFormatReplace(): void
    {
        $index = 'this %s is %sing %ssome';
        $arguments = ['foo', 'bar', 'awe'];

        $response = $this->sut->translateReplace($index, $arguments);

        $this->assertEquals('*this foo is baring awesome*', $response);
    }

    public function testTranslateWelsh(): void
    {
        $this->assertEquals('WELSH*foo*', $this->sut->translate('foo', 'Y'));
    }

    public function testFormatReplaceWelsh(): void
    {
        $index = 'this %s is %sing %ssome';
        $arguments = ['foo', 'bar', 'awe'];

        $response = $this->sut->translateReplace($index, $arguments, 'Y');

        $this->assertEquals('WELSH*this foo is baring awesome*', $response);
    }

    public function testFormatReplaceEscapesArguments(): void
    {
        $response = $this->sut->translateReplace('%s', ['<script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script>', $response);
        $this->assertStringContainsString('&lt;script&gt;', $response);
    }

    public function testFormatReplaceEscapesQuotesSoArgumentsCannotBreakOutOfAnAttribute(): void
    {
        // Translation strings routinely wrap their arguments in markup, e.g. <a href="%s">, so an
        // argument carrying a quote would otherwise escape the attribute.
        $response = $this->sut->translateReplace('%s', ['x" onmouseover="alert(1)']);

        $this->assertStringNotContainsString('onmouseover="alert(1)"', $response);
        $this->assertStringContainsString('&quot;', $response);
    }

    public function testFormatReplaceLeavesOrdinaryValuesIntact(): void
    {
        // Route-generated URLs and plain scalars must survive unchanged.
        $response = $this->sut->translateReplace('%s %s', ['/licence/1/variation', 42]);

        $this->assertStringContainsString('/licence/1/variation', $response);
        $this->assertStringContainsString('42', $response);
    }
}
