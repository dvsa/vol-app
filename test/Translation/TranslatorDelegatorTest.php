<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\Replacements;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TranslatorDelegatorTest extends TestCase
{
    /**
     * @var TranslatorDelegator
     */
    protected $sut;

    /**
     * @var Translator|MockObject
     */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = $this->createMock(Translator::class);

        $this->mockTranslator
            ->method('translate')
            ->willReturnCallback(
                fn($message, $textDomain, $locale) => 'translated-' . $message
            );

        $replacements = new Replacements([
            '{{foo}}' => 'bar',
            '{{bar}}' => 'foo',
        ]);

        $this->sut = new TranslatorDelegator($this->mockTranslator, $replacements);
    }

    public function testImplementsI18nAndValidatorTranslatorInterfaces(): void
    {
        $this->assertInstanceOf(I18nTranslatorInterface::class, $this->sut);
        $this->assertInstanceOf(ValidatorTranslatorInterface::class, $this->sut);
    }

    public function testDoesNotExtendMvcTranslator(): void
    {
        // Guard against accidental reintroduction of the discontinued
        // laminas-mvc-i18n base class.
        $parents = class_parents($this->sut);
        $this->assertNotContains('Laminas\\Mvc\\I18n\\Translator', $parents);
    }

    public function testTranslate(): void
    {
        $this->assertEquals('translated-no-replacements', $this->sut->translate('no-replacements'));

        $this->assertEquals('translated-replace-bar', $this->sut->translate('replace-{{foo}}'));
        $this->assertEquals('translated-replace-foo-bar', $this->sut->translate('replace-{{bar}}-{{foo}}'));
    }

    public function testTranslateNull(): void
    {
        $this->assertEquals('', $this->sut->translate(null));
    }

    public function testTranslateEmptyString(): void
    {
        $this->assertEquals('', $this->sut->translate(''));
    }

    public function testTranslatePluralAppliesReplacements(): void
    {
        $this->mockTranslator
            ->method('translatePlural')
            ->willReturn('plural-{{foo}}');

        $this->assertEquals('plural-bar', $this->sut->translatePlural('one', 'many', 2));
    }

    public function testGetTranslatorReturnsWrappedInstance(): void
    {
        $this->assertSame($this->mockTranslator, $this->sut->getTranslator());
    }

    public function testCallForwardsUnknownMethodsToWrappedTranslator(): void
    {
        $this->mockTranslator->expects($this->once())->method('setLocale');

        // @phpstan-ignore-next-line `setLocale` is forwarded via `__call` to the wrapped translator.
        $this->sut->setLocale('en_GB');
    }
}
