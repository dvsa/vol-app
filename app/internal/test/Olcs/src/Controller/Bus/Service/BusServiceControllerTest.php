<?php

/**
 * Bus Service Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Bus\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerAddEditHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;

/**
 * Bus Service Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusServiceControllerTest extends AbstractHttpControllerTestCase
{
    protected $sut;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();

        $this->sut = new \Olcs\Controller\Bus\Service\BusServiceController();

        parent::setUp();
    }

    public function testRedirectToIndex()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'url' => 'Url',
                'redirect' => 'Redirect'
            ]
        );

        $mockRedirectPlugin = $mockPluginManager->get('redirect', '');
        $mockRedirectPlugin->shouldReceive('toRoute')->with('licence/bus-details', [], [],
            true)->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);
        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }

    public function testProcessLoad()
    {
        $data = [
            'timetableAcceptable' => 'Y',
            'mapSupplied' => 'Y',
            'routeDescription' => 'foo',
            'trcConditionChecked' => 'Y',
            'trcNotes' => 'bar'
        ];
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );
        $mockParamsPlugin = $mockPluginManager->get('params', '');
        $mockParamsPlugin->shouldReceive('fromQuery')->with('case', "")->andReturn('');
        $mockParamsPlugin->shouldReceive('fromRoute')->with('case', "")->andReturn('');

        $this->sut->setPluginManager($mockPluginManager);

        $result = $this->sut->processLoad($data);

        $this->assertArrayHasKey('timetable', $result);
        $this->assertArrayHasKey('conditions', $result);
    }

    public function testGetForm()
    {
        $type = 'foo';

        $mockTableFieldset = m::mock('\Zend\Form\Fieldset');

        $mockConditionsFieldset = m::mock('\Zend\Form\Fieldset');
        $mockConditionsFieldset->shouldReceive('get')->with('table')->andReturn($mockTableFieldset);

        $mockForm = m::mock('\Zend\Form\Form');
        $mockForm->shouldReceive('get')->with('conditions')->andReturn($mockConditionsFieldset);

        $mockFormHelper = m::mock('Common\Form\View\Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->with('BusRegisterService')->andReturn($mockForm);
        $mockFormHelper->shouldReceive('populateFormTable')->with(m::type('string'),
            m::type('array'))->andReturn($mockForm);

        $mockTableService = m::mock('\Common\Service\Table\TableFactory');
        $mockTableService->shouldReceive('prepareTable')->with(m::type('string'),
            m::type('array'))->andReturn(['tabledata']);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn(['Results' => []]);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTableService);
        $mockSl->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockSl);

        $result = $this->sut->getForm($type);

    }
}
