<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa\ViewGenerator;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGenerator\IrhpApplicationViewGenerator;
use Permits\View\Helper\IrhpApplicationSection;
use RuntimeException;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\MvcEvent;

class IrhpApplicationViewGeneratorTest extends MockeryTestCase
{
    private $irhpApplicationViewGenerator;

    public function setUp(): void
    {
        $this->irhpApplicationViewGenerator = new IrhpApplicationViewGenerator();
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'permits/single-question',
            $this->irhpApplicationViewGenerator->getTemplateName()
        );
    }

    public function testGetAdditionalViewVariables(): void
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

        $mvcEvent = m::mock(MvcEvent::class);

        $this->assertEquals(
            $expected,
            $this->irhpApplicationViewGenerator->getAdditionalViewVariables($mvcEvent, $result)
        );
    }

    public function testHandleRedirectionRequest(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(IrhpApplicationViewGenerator::ERR_NOT_SUPPORTED);

        $redirect = m::mock(Redirect::class);

        $this->irhpApplicationViewGenerator->handleRedirectionRequest($redirect, 'foo');
    }
}
