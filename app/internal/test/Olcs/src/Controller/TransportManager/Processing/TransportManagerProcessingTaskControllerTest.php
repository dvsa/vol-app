<?php
/**
 * Transport manager task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\TransportManager\Processing;

use Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController as Sut;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use CommonTest\Traits\MockDateTrait;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;

/**
 * Transport manager task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerProcessingTaskControllerTest extends MockeryTestCase
{
    use MockDateTrait;

    /**
     * @var m\MockInterface|\Zend\Mvc\Controller\PluginManager
     */
    protected $pluginManager;

    /**
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    /**
     * @todo These tests require a real service manager to run, as they are not mocking all dependencies,
     * these tests should be addresses
     */
    public function setUp()
    {
        $this->markTestSkipped();
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->pluginManager = $pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'url' => 'Url']
        );
        $this->sm = Bootstrap::getServiceManager();

        $this->sm->setService(
            'Script',
            m::mock()
                ->shouldReceive('loadFiles')
                ->with(['tasks', 'table-actions', 'forms/filter'])
                ->getMock()
        );
        return parent::setUp();
    }

    /**
     * Test the index action
     * @group task
     */
    public function testIndexActionWithDefaultParams()
    {
        $tmId = 69;

        $sut = m::mock('Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $sut->setServiceLocator($this->sm);

        // mock tmId route param
        $sut->shouldReceive('getFromRoute')->with('transportManager')->andReturn($tmId);
        $sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        // mock date
        $date = '2014-12-13';
        $this->mockDate($date);

        // mock task REST calls
        $defaultTaskSearchParams = [
            'date'               => 'tdt_today',
            'status'             => 'tst_open',
            'sort'               => 'actionDate',
            'order'              => 'ASC',
            'page'               => 1,
            'limit'              => 10,
            'transportManagerId' => $tmId,
            'isClosed'           => false,
            'actionDate'         => '<= 2014-12-13',
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

        // mock TM details rest call
        $restHelperMock->shouldReceive('makeRestCall')
            ->with('TransportManager', 'GET', $tmId, m::any())
            ->andReturn([])
            ->getMock();

        $this->sm->setService('Helper\Rest', $restHelperMock);

        // mock table service
        $this->sm->setService(
            'Table',
            m::mock('\Common\Service\Table\TableBuilder')
                ->shouldReceive('buildTable')
                ->andReturnSelf()
                ->shouldReceive('removeColumn')->twice()
                ->getMock()
        );

        $mockForm = m::mock();
        $sut->shouldReceive('getTaskForm')->andReturn($mockForm);
        $this->sm->setService(
            'viewHelperManager',
            m::mock()
                ->shouldReceive('get')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getContainer')
                            ->andReturn(
                                m::mock()
                                    ->shouldReceive('set')
                                    ->getMock()
                            )
                            ->getMock()
                    )
                ->getMock()
        );

        $sut->shouldReceive('getSearchForm');

        $view = $sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
    }

    /**
     * Test index action with various actions submitted
     * @group task
     * @dataProvider actionDp
     */
    public function testIndexActionWithActionSubmitted($tmId, $taskId, $action, $expectedRouteParams)
    {

        $sut = m::mock('Olcs\Controller\TransportManager\Processing\TransportManagerProcessingTaskController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

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
            ->with('transportManager')
            ->andReturn($tmId);

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
        $tmId   = 69;
        $taskId = 101;

        return [
            [
                $tmId,
                null,
                'Create Task',
                [
                    'action' => 'add',
                    'type'   => 'tm',
                    'typeId' => $tmId,
                ],
            ],
            [
                $tmId,
                $taskId,
                'Re-assign Task',
                [
                    'action' => 'reassign',
                    'type'   => 'tm',
                    'typeId' => $tmId,
                    'task'   => $taskId,
                ],
            ],
            [
                $tmId,
                $taskId,
                'Edit',
                [
                    'action' => 'edit',
                    'type'   => 'tm',
                    'typeId' => $tmId,
                    'task'   => $taskId,
                ],
            ],
            [
                $tmId,
                $taskId,
                'Close',
                [
                    'action' => 'close',
                    'type'   => 'tm',
                    'typeId' => $tmId,
                    'task'   => $taskId,
                ],
            ],
        ];
    }
}
