<?php

namespace OlcsTest\Service\Qa\ViewGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGenerator\IrhpPermitApplicationViewGenerator;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpPermitApplicationViewGeneratorTest extends MockeryTestCase
{
    private $irhpPermitApplicationViewGenerator;

    public function setUp()
    {
        $this->irhpPermitApplicationViewGenerator = new IrhpPermitApplicationViewGenerator();
    }

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'permits/single-question-bilateral',
            $this->irhpPermitApplicationViewGenerator->getTemplateName()
        );
    }

    public function testGetAdditionalViewVariables()
    {
        $countryName = 'Germany';

        $result = [
            'additionalViewData' => [
                'countryName' => $countryName
            ]
        ];

        $expected = [
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'countryName' => $countryName
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->irhpPermitApplicationViewGenerator->getAdditionalViewVariables($result)
        );
    }
}
