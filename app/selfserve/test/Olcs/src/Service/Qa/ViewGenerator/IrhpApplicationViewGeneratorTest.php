<?php

namespace OlcsTest\Service\Qa\ViewGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGenerator\IrhpApplicationViewGenerator;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationViewGeneratorTest extends MockeryTestCase
{
    private $irhpApplicationViewGenerator;

    public function setUp()
    {
        $this->irhpApplicationViewGenerator = new IrhpApplicationViewGenerator();
    }

    public function testGetTemplateName()
    {
        $this->assertEquals(
            'permits/single-question',
            $this->irhpApplicationViewGenerator->getTemplateName()
        );
    }

    public function testGetAdditionalViewVariables()
    {
        $applicationReference = 'OB12345 / 100001';

        $result = [
            'additionalViewData' => [
                'applicationReference' => $applicationReference
            ]
        ];

        $expected = [
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'cancelUrl' => IrhpApplicationSection::ROUTE_PERMITS,
            'application' => [
                'applicationRef' => $applicationReference
            ],
        ];

        $this->assertEquals(
            $expected,
            $this->irhpApplicationViewGenerator->getAdditionalViewVariables($result)
        );
    }
}
