<?php

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Application;

use OlcsTest\Bootstrap;
use Common\Service\Entity\ApplicationEntityService;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Data\CategoryDataService;
use Common\Service\Data\FeeTypeDataService;
use Common\Service\Entity\FeeEntityService;
use CommonTest\Traits\MockDateTrait;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

use Olcs\TestHelpers\Lva\Traits\LvaControllerTestTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * Application Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationControllerTest extends MockeryTestCase
{
    use LvaControllerTestTrait,
        MockDateTrait;

    private $mockParams;
    private $mockRouteParams;
    private $pluginManager;

    /**
     * Required by trait
     */
    protected function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }

    protected function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = $this->getMock(
            '\Olcs\Controller\Application\ApplicationController',
            array('render', 'addErrorMessage', 'redirectToList')
        );
        $this->sut->setServiceLocator($this->sm);
        $this->pluginManager = $this->sut->getPluginManager();
    }

    /**
     * @group application_controller
     */
    public function testCaseAction()
    {
        $this->mockRender();

        $serviceName = 'Olcs\Service\Data\Cases';
        $results = ['id' => '1'];

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['params' => 'Params', 'url' => 'Url']);

        $params = [
            'application' => 1,
            'page'    => 1,
            'sort'    => 'id',
            'order'   => 'desc',
            'limit'   => 10,
            'url'     => $mockPluginManager->get('url')
        ];

        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('application', '')->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('page', 1)->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('sort', 'id')->andReturn('id');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('order', 'desc')->andReturn('desc');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('limit', 10)->andReturn(10);

        // deals with getCrudActionFromPost
        $mockPluginManager->get('params', '')->shouldReceive('fromPost')->with('action')->andReturn(null);

        $dataService = m::mock($serviceName);
        $dataService->shouldReceive('fetchList')->andReturn($results);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $serviceLocator->shouldReceive('get')->with($serviceName)->andReturn($dataService);

        $tableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $tableBuilder->shouldReceive('buildTable')->with('case', $results, $params, false)->andReturn('tableContent');

        $serviceLocator->shouldReceive('get')->with('Table')->andReturn($tableBuilder);

        $sut = $this->sut;
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($serviceLocator);

        $this->assertEquals('licence/cases', $sut->caseAction()->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testEnvironmentalAction()
    {
        $this->mockRender();

        $view = $this->sut->environmentalAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testDocumentAction()
    {
        $this->mockRender();

        $view = $this->sut->documentAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testProcessingAction()
    {
        $this->mockRender();

        $view = $this->sut->processingAction();

        $this->assertEquals('application/index', $view->getTemplate());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithGet()
    {
        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender();

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $formHelper = $this->getMock('\stdClass', ['createForm']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue('FORM'));
        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->grantAction();
        $this->assertEquals('application/grant', $view->getTemplate());
        $this->assertEquals('FORM', $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithCancelButton()
    {
        $id = 7;
        $post = array(
            'form-actions' => array(
                'cancel' => 'foo'
            )
        );

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * @group application_controller
     */
    public function testGrantActionWithPost()
    {
        $id = 7;
        $licenceId = 8;
        $userId = 6;
        $teamId = 1;
        $taskId = 9;
        $feeTypeId = 700;
        $fixedValue = '0.00';
        $fiveYearValue = '10.00';
        $appDate = '2012-01-01';
        $date = date('Y-m-d');
        $this->mockDate($date);

        $post = array();

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $mockApplicationService = $this->getMock(
            '\stdClass',
            [
                'getLicenceIdForApplication',
                'forceUpdate',
                'getApplicationDate'
            ]
        );
        $mockApplicationService->expects($this->once())
            ->method('getLicenceIdForApplication')
            ->with($id)
            ->will($this->returnValue($licenceId));
        $mockApplicationService->expects($this->once())
            ->method('forceUpdate')
            ->with(
                $id,
                array(
                    'status' => ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                    'grantedDate' => $date
                )
            );
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $mockLicenceService = $this->getMock('\stdClass', ['forceUpdate', 'getTypeOfLicenceData']);
        $mockLicenceService->expects($this->once())
            ->method('forceUpdate')
            ->with(
                $licenceId,
                array(
                    'status' => LicenceEntityService::LICENCE_STATUS_GRANTED,
                    'grantedDate' => $date
                )
            );
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockUserService = $this->getMock('\stdClass', ['getCurrentUser']);
        $mockUserService->expects($this->once())
            ->method('getCurrentUser')
            ->will($this->returnValue(array('id' => $userId, 'team' => array('id' => $teamId))));
        $this->sm->setService('Entity\User', $mockUserService);

        $expectedTask = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'taskSubCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE,
            'description' => 'Grant fee due',
            'actionDate' => $date,
            'assignedToUser' => $userId,
            'assignedToTeam' => $teamId,
            'isClosed' => 'N',
            'urgent' => 'N',
            'application' => $id,
            'licence' => $licenceId,
        );

        $mockTaskService = $this->getMock('\stdClass', ['save']);
        $mockTaskService->expects($this->once())
            ->method('save')
            ->with($expectedTask)
            ->will($this->returnValue(array('id' => $taskId)));
        $this->sm->setService('Entity\Task', $mockTaskService);

        $mockApplicationService->expects($this->once())
            ->method('getApplicationDate')
            ->will($this->returnValue($appDate));

        $typeOfLicenceData = array(
            'goodsOrPsv' => LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
            'licenceType' => LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
            'niFlag' => 'N'
        );

        $mockLicenceService->expects($this->once())
            ->method('getTypeOfLicenceData')
            ->with($licenceId)
            ->will($this->returnValue($typeOfLicenceData));

        $feeType = array(
            'id' => $feeTypeId,
            'fixedValue' => $fixedValue,
            'fiveYearValue' => $fiveYearValue,
            'description' => 'fee'
        );

        $mockFeeType = $this->getMock('\stdClass', ['getLatest']);
        $mockFeeType->expects($this->once())
            ->method('getLatest')
            ->with(
                FeeTypeDataService::FEE_TYPE_GRANT,
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
                $appDate,
                false
            )
            ->will($this->returnValue($feeType));
        $this->sm->setService('Data\FeeType', $mockFeeType);

        $feeData = array(
            'amount' => $fiveYearValue,
            'application' => $id,
            'licence' => $licenceId,
            'invoicedDate' => $date,
            'feeType' => $feeTypeId,
            'description' => 'fee for application ' . $id,
            'feeStatus' => FeeEntityService::STATUS_OUTSTANDING,
            'task' => $taskId
        );

        $mockFeeService = $this->getMock('\stdClass', ['save']);
        $mockFeeService->expects($this->once())
            ->method('save')
            ->with($feeData);
        $this->sm->setService('Entity\Fee', $mockFeeService);

        $mockFlashMessenger = $this->getMock('\stdClass', ['addSuccessMessage']);
        $mockFlashMessenger->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The application was granted successfully');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->grantAction());
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithGet()
    {
        $id = 7;

        $this->mockRouteParam('application', $id);

        $this->mockRender();

        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $formHelper = $this->getMock('\stdClass', ['createForm']);
        $formHelper->expects($this->once())
            ->method('createForm')
            ->with('GenericConfirmation')
            ->will($this->returnValue('FORM'));
        $this->sm->setService('Helper\Form', $formHelper);

        $view = $this->sut->undoGrantAction();
        $this->assertEquals('application/undo-grant', $view->getTemplate());
        $this->assertEquals('FORM', $view->getVariable('form'));
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithCancelButton()
    {
        $id = 7;
        $post = array(
            'form-actions' => array(
                'cancel' => 'foo'
            )
        );

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->undoGrantAction());
    }

    /**
     * @group application_controller
     */
    public function testUndoGrantActionWithPost()
    {
        $id = 7;
        $licenceId = 8;
        $post = array();

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $mockApplicationService = $this->getMock(
            '\stdClass',
            [
                'getLicenceIdForApplication',
                'forceUpdate',
                'getApplicationDate'
            ]
        );
        $mockApplicationService->expects($this->once())
            ->method('getLicenceIdForApplication')
            ->with($id)
            ->will($this->returnValue($licenceId));
        $mockApplicationService->expects($this->once())
            ->method('forceUpdate')
            ->with(
                $id,
                array(
                    'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                    'grantedDate' => null
                )
            );
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $mockLicenceService = $this->getMock('\stdClass', ['forceUpdate', 'getTypeOfLicenceData']);
        $mockLicenceService->expects($this->once())
            ->method('forceUpdate')
            ->with(
                $licenceId,
                array(
                    'status' => LicenceEntityService::LICENCE_STATUS_UNDER_CONSIDERATION,
                    'grantedDate' => null
                )
            );
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockFeeService = $this->getMock('\stdClass', ['cancelForLicence']);
        $mockFeeService->expects($this->once())
            ->method('cancelForLicence')
            ->with($licenceId);
        $this->sm->setService('Entity\Fee', $mockFeeService);

        $taskQuery = array(
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'taskSubCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_GRANT_FEE_DUE,
            'licence' => $licenceId,
            'application' => $id
        );

        $mockTaskService = $this->getMock('\stdClass', ['closeByQuery']);
        $mockTaskService->expects($this->once())
            ->method('closeByQuery')
            ->with($taskQuery);
        $this->sm->setService('Entity\Task', $mockTaskService);

        $mockFlashMessenger = $this->getMock('\stdClass', ['addSuccessMessage']);
        $mockFlashMessenger->expects($this->once())
            ->method('addSuccessMessage')
            ->with('The application grant has been undone successfully');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessenger);

        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application', array('application' => 7))
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->undoGrantAction());
    }

    /**
     * @group application_controller
     */
    public function testFeesListActionWithValidPostRedirectsCorrectly()
    {
        $id = 7;
        $post = [
            'id' => [1,2,3]
        ];

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $routeParams = [
            'action' => 'pay-fees',
            'fee' => '1,2,3'
        ];
        $redirect = $this->mockRedirect();
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('lva-application/fees/fee_action', $routeParams)
            ->will($this->returnValue('REDIRECT'));

        $this->assertEquals('REDIRECT', $this->sut->feesAction());
    }

    /**
     * @group application_controller
     */
    public function testFeesListActionWithInvalidPostRedirectsCorrectly()
    {
        $id = 7;
        $post = [];

        $this->mockRouteParam('application', $id);

        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new \Zend\Stdlib\Parameters($post));

        $this->sut->expects($this->once())
            ->method('redirectToList')
            ->will($this->returnValue('REDIRECT'));

        $this->sut->expects($this->once())
            ->method('addErrorMessage');

        $this->assertEquals('REDIRECT', $this->sut->feesAction());
    }

    public function testPayFeesActionWithGet()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $validatorClosure = function ($input) {
            $this->assertEquals(15.5, $input->getMax());
            $this->assertEquals(true, $input->getInclusive());
        };

        $inputFilter = m::mock()
            ->shouldReceive(['get' => 'details'])
            ->andReturn(
                m::mock()
                ->shouldReceive(['get' => 'received'])
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getValidatorChain')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('addValidator')
                        ->andReturnUsing($validatorClosure)
                        ->getMock()
                    )
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $form = m::mock()
            ->shouldReceive('get')
            ->with('details')
            ->andReturn(
                m::mock()
                ->shouldReceive('get')
                ->with('maxAmount')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('setValue')
                    ->with('£15.50')
                    ->getMock()
                )
                ->getMock()
            )
            ->shouldReceive('getInputFilter')
            ->andReturn($inputFilter)
            ->getMock();

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1,2')
            ->shouldReceive('getForm')
            ->with('FeePayment')
            ->andReturn($form)
            ->shouldReceive('renderView')
            ->andReturn('renderView');

        $fees = [
            [
                'amount' => 5.5,
                'feeStatus' => [
                    'id' => 'lfs_ot'
                ]
            ], [
                'amount' => 10,
                'feeStatus' => [
                    'id' => 'lfs_ot'
                ]
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fees[0])
            ->shouldReceive('getOverview')
            ->with('2')
            ->andReturn($fees[1]);

        $this->assertEquals(
            'renderView',
            $this->sut->payFeesAction()
        );
    }

    public function testPayFeesActionWithInvalidFeeStatuses()
    {
        $this->mockController(
            '\Olcs\Controller\Application\ApplicationController'
        );

        $this->sut->shouldReceive('params')
            ->with('fee')
            ->andReturn('1')
            ->shouldReceive('redirectToList')
            ->andReturn('redirect')
            ->shouldReceive('addErrorMessage');

        $fee = [
            'amount' => 5.5,
            'feeStatus' => [
                'id' => 'lfs_pd'
            ]
        ];
        $this->mockEntity('Fee', 'getOverview')
            ->with('1')
            ->andReturn($fee);

        $this->assertEquals(
            'redirect',
            $this->sut->payFeesAction()
        );
    }

    /**
     * Helper method
     * @to-do when these helper methods are required in more than 1 place, we need to abstract them away
     */
    protected function mockRouteParam($name, $value)
    {
        $this->mockRouteParams[$name] = $value;

        if ($this->mockParams === null) {
            $this->mockParams = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', array('__invoke'));

            $this->mockParams->expects($this->any())
                ->method('__invoke')
                ->will($this->returnCallback(array($this, 'getRouteParam')));

            $this->pluginManager->setService('params', $this->mockParams);
        }
    }

    /**
     * Helper method
     */
    public function getRouteParam($name)
    {
        return isset($this->mockRouteParams[$name]) ? $this->mockRouteParams[$name] : null;
    }

    /**
     * Helper method
     */
    protected function mockRender()
    {
        $this->sut->expects($this->once())
            ->method('render')
            ->will(
                $this->returnCallback(
                    function ($view) {
                        return $view;
                    }
                )
            );
    }

    /**
     * Helper method
     */
    protected function mockRedirect()
    {
        $mockRedirect = $this->getMock('\Zend\Mvc\Controller\Plugin\Redirect', array('toRoute'));
        $this->pluginManager->setService('Redirect', $mockRedirect);
        return $mockRedirect;
    }
}
