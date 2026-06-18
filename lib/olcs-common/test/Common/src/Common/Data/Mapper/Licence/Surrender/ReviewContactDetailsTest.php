<?php

namespace CommonTest\Common\Data\Mapper\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\Service\Helper\TranslationHelperService;
use CommonTest\Common\Data\Mapper\Licence\Surrender\ReviewContactDetailsMocksAndExpectationsTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Mvc\Controller\Plugin\Url;

class ReviewContactDetailsTest extends MockeryTestCase
{
    use ReviewContactDetailsMocksAndExpectationsTrait;

    public function testMakeSections(): void
    {
        $mockTranslator = m::mock(TranslationHelperService::class);
        $mockUrlHelper = m::mock(Url::class);
        $mockLicence = $this->mockLicence();

        $this->mockTranslatorForContactDetails($mockTranslator);
        $this->mockTranslatorForCorrespondenceAddress($mockTranslator);
        $this->mockTranslatorForLicenceDetails($mockTranslator);

        $this->mockUrlHelperFromRoute($mockUrlHelper, 'licence/surrender/address-details', 2);

        $sections = ReviewContactDetails::makeSections($mockLicence, $mockUrlHelper, $mockTranslator);

        $expected = [
            $this->expectedForLicenceDetails(),
            $this->expectedForCorrespondenceAddress(),
            $this->expectedForContactDetails(),
        ];

        $this->assertSame($expected, $sections);
    }
}
