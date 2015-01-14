<?php

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewControllerTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\Lva\Licence\OverviewController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = m::mock('\Zend\ServiceManager\ServiceManager')->makePartial();
        $this->sm->setAllowOverride(true);

        $this->sut->setServiceLocator($this->sm);
    }

    public function testCreateVariationAction()
    {
        $licenceId = 3;
        $varId = 5;

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId)
            ->andReturn($varId);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => $varId])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariationAction());
    }
}
