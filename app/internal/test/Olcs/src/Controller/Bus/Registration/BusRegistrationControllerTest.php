<?php

/**
 * Bus Registration Controller Test
 *
 */

namespace OlcsTest\Controller\Bus\Registration;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use OlcsTest\Bootstrap;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\Controller\Traits\ControllerTestTrait;

/**
 * Bus Registration Controller Test
 *
 */
class BusRegistrationControllerTest extends MockeryTestCase
{
    use ControllerTestTrait;

    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    protected $testClass = 'Olcs\Controller\Bus\Registration\BusRegistrationController';

    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    public function testAddAction()
    {
        $licenceId = 1;

        $this->mockController($this->testClass);

        $this->sut->shouldReceive('getFromRoute')
            ->with('licence')
            ->andReturn($licenceId);

        $this->mockEntity('Licence', 'getById')
            ->with($licenceId)
            ->andReturn(['id' => $licenceId, 'licNo' => 'LICNO']);

        $busRegistrationService = m::mock('\StdClass')
            ->shouldReceive('createNew')
            ->andReturn([])
            ->getMock();
        $this->sut->setBusRegistrationService($busRegistrationService);

        $this->mockEntity('BusReg', 'findMostRecentRouteNoByLicence')
            ->with($licenceId)
            ->andReturn(['routeNo' => 2]);

        $this->mockEntity('BusReg', 'save')
            ->with(['routeNo' => 3, 'regNo' => 'LICNO/3'])
            ->andReturn(['id' => 999]);

        $this->sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRouteAjax')
                    ->with('licence/bus-details/service', ['busRegId' => 999], [], true)
                    ->andReturn(true)
                    ->getMock()
            );

        $this->sut->addAction();
    }

    public function testCreateVariationAction()
    {
        $busRegId = 1;
        $busReg = ['id' => $busRegId];
        $busRegVariation = ['id' => 2];

        $this->mockController($this->testClass);

        $this->sut->shouldReceive('getFromRoute')
            ->with('busRegId')
            ->andReturn($busRegId);

        $this->mockEntity('BusReg', 'getDataForVariation')
            ->with($busRegId)
            ->andReturn($busReg);

        $busRegistrationService = m::mock('\StdClass')
            ->shouldReceive('createVariation')
            ->with($busReg)
            ->andReturn($busRegVariation)
            ->getMock();
        $this->sut->setBusRegistrationService($busRegistrationService);

        $this->mockEntity('BusReg', 'save')
            ->with($busRegVariation)
            ->andReturn(['id' => 999]);

        $this->sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRouteAjax')
                    ->with('licence/bus-details/service', ['busRegId' => 999], [], true)
                    ->andReturn(true)
                    ->getMock()
            );

        $this->sut->createVariationAction();
    }
}
