<?php
namespace OlcsTest\Controller\Traits;

use Mockery as m;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Class CloseActionTraitTest
 * @package OlcsTest\Controller\Traits
 */
class CloseActionTraitTest extends \PHPUnit_Framework_TestCase
{
    public $sut;

    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Submission\SubmissionController();
    }

    /**
     * Tests when the close action form has been submitted
     */
    public function testCloseActionFormDisplay()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );
        $mockView = new \Zend\View\Model\ViewModel();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);

        $mockPluginManager->shouldReceive('get')->with('confirm', '')->andReturn($mockView);
        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->closeAction();
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }

    /**
     * Tests when the close action form has been submitted
     */
    public function testCloseActionFormSubmit()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'params' => 'Params',
                'confirm' => 'Confirm'
            ]
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            '',
            m::type('array'),
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');
        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);
        $mockPluginManager->shouldReceive('get')->with('params')->andReturn($mockParams);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('closeEntity')
            ->with($id)
            ->andReturnNull();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->closeAction());
    }

    /**
     * Tests when the reopen action form has been submitted
     */
    public function testReopenActionFormDisplay()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );
        $mockView = new \Zend\View\Model\ViewModel();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);

        $mockPluginManager->shouldReceive('get')->with('confirm', '')->andReturn($mockView);
        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->reopenAction();
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
    }

    /**
     * Tests when the close action form has been submitted
     */
    public function testReopenActionFormSubmit()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'redirect' => 'Redirect',
                'params' => 'Params',
                'confirm' => 'Confirm'
            ]
        );

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            '',
            m::type('array'),
            ['code' => '303'],
            true
        )->andReturn('redirectResponse');
        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);
        $mockPluginManager->shouldReceive('get')->with('params')->andReturn($mockParams);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('reopenEntity')
            ->with($id)
            ->andReturnNull();

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->reopenAction());
    }

    public function testGenerateCloseActionButtonArrayForReopenButton()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);
        $mockPluginManager->shouldReceive('get')->with('params')->andReturn($mockParams);

        $mockRouteMatch = m::mock('Zend\Mvc\RouteMatch');
        $mockRouteMatch->shouldReceive('getParams')->andReturn(['action' => 'foo']);
        $mockRouteMatch->shouldReceive('getMatchedRouteName')->andReturn('bar');

        $mockEvent = m::mock('Zend\Mvc\MvcEvent');
        $mockEvent->shouldReceive('getRouteMatch')->andReturn($mockRouteMatch);

        $mockDataService = m::mock('Olcs\Service\Data\Submission');
        $mockApplication = m::mock('Zend\Mvc\Application');
        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockEvent);


        $mockDataService->shouldReceive('canReopen')
            ->with($id)
            ->andReturn(true);
        $mockDataService->shouldReceive('getReopenButton')
            ->with($id)
            ->andReturn('reopenButton');

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Submission')
            ->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')
            ->with('Application')
            ->andReturn($mockApplication);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->generateCloseActionButtonArray();
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('params', $result);
    }

    public function testGenerateCloseActionButtonArrayForCloseButton()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);
        $mockPluginManager->shouldReceive('get')->with('params')->andReturn($mockParams);

        $mockRouteMatch = m::mock('Zend\Mvc\RouteMatch');
        $mockRouteMatch->shouldReceive('getParams')->andReturn(['action' => 'foo']);
        $mockRouteMatch->shouldReceive('getMatchedRouteName')->andReturn('bar');

        $mockEvent = m::mock('Zend\Mvc\MvcEvent');
        $mockEvent->shouldReceive('getRouteMatch')->andReturn($mockRouteMatch);
        $mockApplication = m::mock('Zend\Mvc\Application');
        $mockApplication->shouldReceive('getMvcEvent')
            ->andReturn($mockEvent);

        $mockDataService = m::mock('Olcs\Service\Data\Submission');
        $mockDataService->shouldReceive('canReopen')
            ->with($id)
            ->andReturn(false);
        $mockDataService->shouldReceive('canClose')
            ->with($id)
            ->andReturn(true);
        $mockDataService->shouldReceive('getCloseButton')
            ->with($id)
            ->andReturn('closeButton');

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Submission')
            ->andReturn($mockDataService);
        $mockServiceManager->shouldReceive('get')
            ->with('Application')
            ->andReturn($mockApplication);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->generateCloseActionButtonArray();
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('route', $result);
        $this->assertArrayHasKey('params', $result);
    }

    /**
     * Test for no button Required (data entity is not closeable, or does not meet criteria to be closed
     */
    public function testGenerateCloseActionButtonArrayForNoButton()
    {
        $identifier = 'submission';
        $id = 99;
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with($identifier)->andReturn($id);
        $mockPluginManager->shouldReceive('get')->with('params')->andReturn($mockParams);

        $mockDataService = m::mock('Olcs\Service\Data\Submission');
        $mockDataService->shouldReceive('canReopen')
            ->with($id)
            ->andReturn(false);
        $mockDataService->shouldReceive('canClose')
            ->with($id)
            ->andReturn(false);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')
            ->with('Olcs\Service\Data\Submission')
            ->andReturn($mockDataService);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertNull($this->sut->generateCloseActionButtonArray());
    }
}
