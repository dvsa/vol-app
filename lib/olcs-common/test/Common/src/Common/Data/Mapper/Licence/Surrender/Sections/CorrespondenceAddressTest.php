<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender\Sections;

use Common\Data\Mapper\Licence\Surrender\Sections\CorrespondenceAddress;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Data\Mapper\Licence\Surrender\ReviewContactDetailsMocksAndExpectationsTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Url;

class CorrespondenceAddressTest extends MockeryTestCase
{
    use ReviewContactDetailsMocksAndExpectationsTrait;

    public function testMakeQuestions(): void
    {
        $mockTranslator = m::mock(TranslationHelperService::class);
        $mockUrlHelper = m::mock(Url::class);
        $this->mockTranslatorForCorrespondenceAddress($mockTranslator);

        $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/address-details', 1);

        $mockLicence = $this->mockLicence();

        $sut = new CorrespondenceAddress($mockLicence, $mockUrlHelper, $mockTranslator);
        $section = $sut->makeSection();

        $expected = $this->expectedForCorrespondenceAddress();

        $this->assertSame($expected, $section);
    }
}
