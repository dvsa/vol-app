<?php

/**
 * Translation Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Helper;

use Common\Service\Helper\TranslationHelperService;
use Laminas\I18n\Translator\Translator;

/**
 * Translation Helper Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TranslationHelperServiceTest extends \PHPUnit\Framework\TestCase
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
        $this->mockTranslator->expects($this->any())
            ->method('translate')
            ->will($this->returnCallback(fn($message, $domain, $locale) => $this->translate($message, $domain, $locale)));

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

    /**
     * @group helper_service
     * @group translation_helper_service
     */
    public function testGetTranslator(): void
    {
        $this->assertSame($this->mockTranslator, $this->sut->getTranslator());
    }

    /**
     * @group helper_service
     * @group translation_helper_service
     */
    public function testTranslate(): void
    {
        $this->assertEquals('*foo*', $this->sut->translate('foo'));
    }

    /**
     * @group helper_service
     * @group translation_helper_service
     */
    public function testWrapTranslation(): void
    {
        $format = 'This is a wrapped <div>%s</div>';
        $translation = 'translation';
        $expected = 'This is a wrapped <div>*translation*</div>';

        $this->assertEquals($expected, $this->sut->wrapTranslation($format, $translation));
    }

    /**
     * @group helper_service
     * @group translation_helper_service
     */
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

    /**
     * @group helper_service
     * @group translation_helper_service
     */
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
}
