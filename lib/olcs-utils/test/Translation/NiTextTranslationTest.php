<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class NiTextTranslationTest extends TestCase
{
    /**
     * @var Translator|MockObject
     */
    protected $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('getLocale')->willReturn('en_GB');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('niFlagProvider')]
    public function testSetLocaleForNiFlag($niFlag, $expected, $expectedFallback)
    {
        $this->translator->expects($niFlag === 'N' ? $this->never() : $this->once())->method('setLocale')->with($expected);
        $this->translator->expects($niFlag === 'N' ? $this->never() : $this->once())->method('setFallbackLocale')->with($expectedFallback);

        $this->getService()->setLocaleForNiFlag($niFlag);
    }

    public static function niFlagProvider(): \Iterator
    {
        yield [
            'N',
            'en_GB',
            null
        ];
        yield [
            'Y',
            'en_NI',
            'en_GB'
        ];
    }

    protected function getService(): NiTextTranslation
    {
        $serviceManager = $this->createStub(ServiceManager::class);
        $serviceManager->method('get')->willReturnMap([
            ['translator', $this->translator]
        ]);

        $serviceManager->method('has')->willReturnMap([
            ['getPlaceholder', true],
        ]);

        $niTextTranslation = new NiTextTranslation();
        $niTextTranslation->__invoke($serviceManager, NiTextTranslation::class);

        return $niTextTranslation;
    }
}
