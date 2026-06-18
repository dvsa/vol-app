<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender\Sections;

use Common\Data\Mapper\Licence\Surrender\Sections\ContactDetails;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Data\Mapper\Licence\Surrender\ReviewContactDetailsMocksAndExpectationsTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Url;

class ContactDetailsTest extends MockeryTestCase
{
    use ReviewContactDetailsMocksAndExpectationsTrait;

    public function testMakeQuestions(): void
    {
        $mockTranslator = m::mock(TranslationHelperService::class);
        $mockUrlHelper = m::mock(Url::class);
        $this->mockTranslatorForContactDetails($mockTranslator);

        $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/address-details', 1);

        $mockLicence = $this->mockLicence();

        $sut = new ContactDetails($mockLicence, $mockUrlHelper, $mockTranslator);
        $section = $sut->makeSection();

        $expected = $this->expectedForContactDetails();

        $this->assertSame($expected, $section);
    }
}
