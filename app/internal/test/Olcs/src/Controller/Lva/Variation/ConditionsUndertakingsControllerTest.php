<?php

/**
 * Internal Variation Conditions Undertakings Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Variation;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Olcs\Controller\Lva\Variation\ConditionsUndertakingsController;

/**
 * Internal Variation Conditions Undertakings Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsControllerTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    protected $adapter;

    protected $pm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->adapter = m::mock('\Common\Controller\Lva\Interfaces\AdapterInterface');
        $this->pm = m::mock('\Zend\Mvc\Controller\PluginManager')->makePartial();

        $this->sut = new ConditionsUndertakingsController();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setAdapter($this->adapter);
        $this->sut->setPluginManager($this->pm);
    }

    public function testRestoreAction()
    {
        $this->markTestSkipped();

        // Params
        $childId = '12,13';
        $id = 123;

        // Mocks
        $mockParams = m::mock('\Zend\Mvc\Controller\Plugin\Params')->makePartial();
        $this->pm->setService('params', $mockParams);
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        // Expectations
        $mockParams->shouldReceive('__invoke')
            ->with('child_id')
            ->andReturn($childId)
            ->shouldReceive('__invoke')
            ->with('application')
            ->andReturn($id);

        $this->adapter->shouldReceive('restore')
            ->with(12, 123)
            ->andReturn(false)
            ->shouldReceive('restore')
            ->with(13, 123)
            ->andReturn(true);

        $mockFlashMessenger->shouldReceive('addSuccessMessage')
            ->with('generic-restore-success');

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['application' => 123])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->restoreAction());
    }

    public function testRestoreActionWithNoAction()
    {
        $this->markTestSkipped();

        // Params
        $childId = '12,13';
        $id = 123;

        // Mocks
        $mockParams = m::mock('\Zend\Mvc\Controller\Plugin\Params')->makePartial();
        $this->pm->setService('params', $mockParams);
        $mockFlashMessenger = m::mock();
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);
        $mockRedirect = m::mock('\Common\Controller\Plugin\Redirect')->makePartial();
        $this->pm->setService('redirect', $mockRedirect);

        // Expectations
        $mockParams->shouldReceive('__invoke')
            ->with('child_id')
            ->andReturn($childId)
            ->shouldReceive('__invoke')
            ->with('application')
            ->andReturn($id);

        $this->adapter->shouldReceive('restore')
            ->with(12, 123)
            ->andReturn(false)
            ->shouldReceive('restore')
            ->with(13, 123)
            ->andReturn(false);

        $mockFlashMessenger->shouldReceive('addInfoMessage')
            ->with('generic-nothing-updated');

        $mockRedirect->shouldReceive('toRouteAjax')
            ->with(null, ['application' => 123])
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->restoreAction());
    }
}
