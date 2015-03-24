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

    public function testEditAction()
    {
        $busRegId = 1;

        $this->mockController($this->testClass);

        $this->sut->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn($busRegId);

        $this->sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRouteAjax')
                    ->with('licence/bus-details/service', ['busRegId' => $busRegId], [], true)
                    ->andReturn(true)
                    ->getMock()
            );

        $this->sut->editAction();
    }

    public function testCreateVariationAction()
    {
        $busRegId = 1;
        $busReg = ['id' => $busRegId];
        $busReg['regNo'] = 'LICNO\7';
        $busRegVariation = ['id' => 2];

        $this->mockController($this->testClass);

        $this->sut->shouldReceive('getFromRoute')
            ->with('busRegId')
            ->andReturn($busRegId);

        $this->mockEntity('BusReg', 'getDataForVariation')
            ->with($busRegId)
            ->andReturn($busReg);

        $this->services['Entity\BusReg']->shouldReceive('findMostRecentByIdentifier')->andReturn(['id'=>75]);

        $busRegistrationService = m::mock('\StdClass')
            ->shouldReceive('createVariation')
            ->with($busReg, ['id'=>75])
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

    public function testCreateCancellationAction()
    {
        $busRegId = 1;
        $busReg = ['id' => $busRegId];
        $busReg['regNo'] = 'LICNO\7';
        $busRegVariation = ['id' => 2];

        $this->mockController($this->testClass);

        $this->sut->shouldReceive('getFromRoute')
            ->with('busRegId')
            ->andReturn($busRegId);

        $this->mockEntity('BusReg', 'getDataForVariation')
            ->with($busRegId)
            ->andReturn($busReg);

        $this->services['Entity\BusReg']->shouldReceive('findMostRecentByIdentifier')->andReturn(['id'=>75]);

        $busRegistrationService = m::mock('\StdClass')
            ->shouldReceive('createCancellation')
            ->with($busReg, ['id'=>75])
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

        $this->sut->createCancellationAction();
    }
}
