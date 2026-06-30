<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender\Sections;

use Common\Data\Mapper\Licence\Surrender\Sections\LicenceDetails;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Data\Mapper\Licence\Surrender\ReviewContactDetailsMocksAndExpectationsTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Url;

class LicenceDetailsTest extends MockeryTestCase
{
    use ReviewContactDetailsMocksAndExpectationsTrait;

    public function testMakeQuestions(): void
    {
        $mockTranslator = m::mock(TranslationHelperService::class);
        $mockUrlHelper = m::mock(Url::class);
        $this->mockTranslatorForLicenceDetails($mockTranslator);

        $mockLicence = $this->mockLicence();

        $sut = new LicenceDetails($mockLicence, $mockUrlHelper, $mockTranslator);
        $section = $sut->makeSection();

        $expected = $this->expectedForLicenceDetails();

        $this->assertSame($expected, $section);
    }
}
