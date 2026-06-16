<?php

namespace Dvsa\OlcsTest\Utils\Translation;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\I18n\Translator\Translator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NiTextTranslationTest extends TestCase
{
    /**
     * @var NiTextTranslation
     */
    protected $sut;

    /**
     * @var Translator|MockObject
     */
    protected $translator;

    public function setUp(): void
    {
        $this->translator = $this->createMock(Translator::class);
        $this->translator->method('getLocale')->willReturn('en_GB');
    }

    /**
     * @dataProvider niFlagProvider
     */
    public function testSetLocaleForNiFlag($niFlag, $expected, $expectedFallback)
    {
        $this->translator->expects($niFlag === 'N' ? $this->never() : $this->once())->method('setLocale')->with($expected);
        $this->translator->expects($niFlag === 'N' ? $this->never() : $this->once())->method('setFallbackLocale')->with($expectedFallback);

        $this->getService()->setLocaleForNiFlag($niFlag);
    }

    public function niFlagProvider()
    {
        return [
            [
                'N',
                'en_GB',
                null
            ],
            [
                'Y',
                'en_NI',
                'en_GB'
            ]
        ];
    }

    protected function getService(): NiTextTranslation
    {
        $serviceManager = $this->createMock(ServiceManager::class);
        $serviceManager->method('get')->willReturnMap([
            ['translator', $this->translator]
        ]);

        $serviceManager->method('has')->with('getPlaceholder')->willReturn(true);

        $niTextTranslation = new NiTextTranslation();
        $niTextTranslation->__invoke($serviceManager, NiTextTranslation::class);

        return $niTextTranslation;
    }
}
