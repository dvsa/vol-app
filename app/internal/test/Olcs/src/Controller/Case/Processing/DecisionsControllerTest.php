<?php

namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Processing\DecisionsController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerDetailsActionHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

/**
 * Class DecisionsControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DecisionsControllerTest extends MockeryTestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerDetailsActionHelper
     */
    protected $detailsHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function setUp()
    {
        $this->sut = new DecisionsController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->detailsHelper = new ControllerDetailsActionHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();

        parent::setUp();
    }

    /**
     * Tests the details action
     */
    public function testDetailsAction()
    {
        $id = 1;
        $mockRestData = ['id' => $id];
        $expectedResult = ['id' => $id];
        $placeholderName = 'case';

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['case' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->detailsAction());
    }

    /**
     * Tests the details action when no data is found
     */
    public function testDetailsActionNotFound()
    {
        $id = null;
        $mockRestData = false;
        $expectedResult = null;
        $placeholderName = 'case';

        $this->sut->setPluginManager($this->detailsHelper->getPluginManager(['case' => $id]));

        $mockServiceManager = $this->detailsHelper->getServiceManager($expectedResult, $mockRestData, $placeholderName);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->setEvent($this->detailsHelper->getNotFoundEvent());

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $this->sut->detailsAction());
    }

    /**
     * Checks that the decision id is correctly appended to the form data
     */
    public function testGetDataForFormEdit()
    {
        $decision = 2;
        $id = 1;
        $action = 'edit';
        $mockRestData = ['id' => $id];

        $expected = [
            'id' => $id,
            'fields' => [
                'id' => $id,
                'decision' => $decision
            ],
            'base' => [
                'id' => $id,
                'fields' => [
                    'id' => $id,
                ],
            ]
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('decision')->andReturn($decision);
        $mockParams->shouldReceive('fromRoute')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $this->sut->setPluginManager($mockPluginManager);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->getDataForForm();

        $this->assertEquals($data, $expected);
    }

    /**
     * Checks that the decision id is correctly appended to the form data
     */
    public function testGetDataForFormAdd()
    {
        $decision = 2;
        $id = 1;
        $case = 24;
        $action = 'add';
        $mockRestData = false;

        $expected = [
            'case' => $case,
            'fields' => [
                'case' => $case,
                'decision' => $decision
            ],
            'base' => [
                'case' => $case
            ]
        ];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params'
            ]
        );

        //rest call to return prohibition data
        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn($mockRestData);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('decision')->andReturn($decision);
        $mockParams->shouldReceive('fromRoute')->with('action')->andReturn($action);
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);
        $mockParams->shouldReceive('fromQuery')->with('case', null)->andReturn($case);
        $this->sut->setPluginManager($mockPluginManager);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('action' => 'not-found'));
        $this->sut->setEvent($event);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->setServiceLocator($mockServiceManager);

        $data = $this->sut->getDataForForm();

        $this->assertEquals($data, $expected);
    }

    /**
     * Tests getFormName
     *
     * @param $decisionType
     * @param $formName
     *
     * @dataProvider getFormNameProvider
     */
    public function testGetFormName($decisionType, $formName)
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('decision')->andReturn($decisionType);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals($formName, $this->sut->getFormName());
    }

    /**
     * Makes sure an exception is thrown if the decision type is invalid.
     *
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testGetFormNameThrowsNotFoundExceptionIfInvalid()
    {
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('decision')->andReturn('');

        $this->sut->setPluginManager($mockPluginManager);

        $this->sut->getFormName();
    }

    /**
     * Data provider for testGetFormName
     *
     * @return array
     */
    public function getFormNameProvider()
    {
        return [
            ['tm_decision_rl', 'TmCaseUnfit'],
            ['tm_decision_rnl', 'TmCaseRepute']
        ];
    }

    /**
     * Tests redirectToIndex
     */
    public function testRedirectToIndex()
    {
        $case = 1;

        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'redirect' => 'Redirect']
        );

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($case);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRouteAjax')->with(
            'processing_decisions',
            ['action'=>'details', 'case' => $case],
            ['code' => '303'], false
        )->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);

        $this->assertEquals('redirectResponse', $this->sut->redirectToIndex());
    }
}
