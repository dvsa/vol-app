<?php
/**
 * Bus Registration task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Bus\Processing;

use Olcs\Controller\Bus\Processing\BusProcessingTaskController as Sut;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;

/**
 * Bus Registration task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class BusProcessingTaskControllerTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var m\MockInterface|\Zend\Mvc\Controller\PluginManager
     */
    protected $pluginManager;

    /**
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $serviceLocator;

    public function setUp()
    {
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->pluginManager = $pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'url' => 'Url']
        );
        $this->serviceLocator = \OlcsTest\Bootstrap::getServiceManager();
        return parent::setUp();
    }

    /**
     * Test the index action
     * @group task
     */
    public function testIndexActionWithDefaultParams()
    {
        $busRegId  = 69;
        $licenceId = 110;

        // mock tmId route param
        $mockParams = $this->pluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('busRegId')->andReturn($busRegId);
        $mockParams->shouldReceive('fromRoute')->with('licence')->andReturn($licenceId);

        // mock task REST calls
        $today = $this->serviceLocator->get('Helper\Date')->getDate('Y-m-d');
        $defaultTaskSearchParams = [
            'date'       => 'tdt_today',
            'status'     => 'tst_open',
            'sort'       => 'actionDate',
            'order'      => 'ASC',
            'page'       => 1,
            'limit'      => 10,
            'licenceId'  => $licenceId,
            'isClosed'   => false,
            'actionDate' => '<= '.$today ,
        ];

        $restHelperMock = m::mock('Common\Service\Helper\RestHelperService')
            ->shouldReceive('makeRestCall')
                ->withArgs(
                    [
                        'TaskSearchView',
                        'GET',
                        $defaultTaskSearchParams,
                        null
                    ]
                )->andReturn([])
            ->shouldReceive('makeRestCall')
                ->with('Team', 'GET', m::any(), '')
                ->andReturn([])
            ->shouldReceive('makeRestCall')
                ->with('User', 'GET', m::any(), '')
                ->andReturn([])
            ->shouldReceive('makeRestCall')
                ->with('Category', 'GET', m::any(), '')
                ->andReturn([])
            ->shouldReceive('makeRestCall')
                ->with('SubCategory', 'GET', m::any(), '')
                ->andReturn([])
            ->getMock();

        // mock Bus Reg details rest call
        $restHelperMock->shouldReceive('makeRestCall')
            ->with(
                'BusReg',
                'GET',
                [
                    'id' => 69,
                    'bundle' => '{"children":{"licence":{"properties":"ALL","children":["organisation"]},"status":{"properties":"ALL"}}}'
                ],
                m::any()
            )
            ->andReturn(
                [
                    'id' => 123,
                    'regNo' => 'BR1234',
                    'variationNo' => 99,
                    'licence' => [
                        'id' => 110,
                        'licNo' => 'AB1234',
                        'organisation' => ['name' => 'org1'],
                    ],
                    'status' => ['description' => 'status'],
                ]
            )
            ->getMock();

        $this->serviceLocator->setService('Helper\Rest', $restHelperMock);

        // mock table service
        $this->serviceLocator->setService(
            'Table',
            m::mock('\Common\Service\Table\TableBuilder')
                ->shouldReceive('buildTable')
                ->andReturnSelf()
                ->shouldReceive('removeColumn')->twice()
                ->getMock()
        );

        // mock nav helper
        $nav = m::mock('\StdClass')
            ->shouldReceive('findOneBy')
            ->with('id', 'licence_bus_processing')
            ->getMock();
        $this->serviceLocator->setService('Navigation', $nav);

        $sut = new Sut;
        $sut->setPluginManager($this->pluginManager);
        $sut->setServiceLocator($this->serviceLocator);

        $view = $sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
    }

    /**
     * Test index action with various actions submitted
     * @group task
     * @dataProvider actionDp
     */
    public function testIndexActionWithActionSubmitted($busRegId, $taskId, $action, $expectedRouteParams)
    {

        $sut = m::mock('\Olcs\Controller\Bus\Processing\BusProcessingTaskController')
            ->makePartial();

        $sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock('StdClass')
                    ->shouldReceive('isPost')
                    ->andReturn(true)
                    ->getMock()
            );

        $sut->shouldReceive('params')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('fromPost')
                        ->with('action')
                        ->andReturn($action)
                    ->shouldReceive('fromPost')
                        ->with('id')
                        ->andReturn([$taskId])
                    ->getMock()
            );

        $sut->shouldReceive('getFromRoute')
            ->with('busRegId')
            ->andReturn($busRegId);

        $sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRoute')
                    ->with('task_action', $expectedRouteParams)
                    ->andReturn('thisistheredirect')
                    ->getMock()
            );

        $response = $sut->indexAction();

        $this->assertEquals('thisistheredirect', $response);
    }

    /**
     * @return array
     */
    public function actionDp()
    {
        $busRegId  = 69;
        $licenceId = 110;
        $taskId    = 101;

        return [
            [
                $busRegId,
                null,
                'Create Task',
                [
                    'action' => 'add',
                    'type'   => 'busreg',
                    'typeId' => $busRegId,
                ],
            ],
            [
                $busRegId,
                $taskId,
                'Re-assign Task',
                [
                    'action' => 'reassign',
                    'type'   => 'busreg',
                    'typeId' => $busRegId,
                    'task'   => $taskId,
                ],
            ],
            [
                $busRegId,
                $taskId,
                'Edit',
                [
                    'action' => 'edit',
                    'type'   => 'busreg',
                    'typeId' => $busRegId,
                    'task'   => $taskId,
                ],
            ],
            [
                $busRegId,
                $taskId,
                'Close',
                [
                    'action' => 'close',
                    'type'   => 'busreg',
                    'typeId' => $busRegId,
                    'task'   => $taskId,
                ],
            ],
        ];
    }
}
