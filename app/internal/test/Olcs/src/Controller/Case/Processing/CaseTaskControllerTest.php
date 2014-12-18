<?php
/**
 * Case task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller;

use Olcs\Controller\Cases\Processing\TaskController as Sut;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use CommonTest\Traits\MockDateTrait;
use Mockery as m;

/**
 * Case task controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CaseTaskControllerTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
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

    public function setUp()
    {
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->pluginManager = $pluginManagerHelper->getMockPluginManager(
            ['params' => 'Params', 'url' => 'Url']
        );
        $this->sm = \OlcsTest\Bootstrap::getServiceManager();
        return parent::setUp();
    }

    /**
     * Test the index action
     * @group task
     */
    public function testIndexActionWithDefaultParams()
    {
        $caseId    = 28;
        $licenceId = 7;

        // mock case id route param
        $mockParams = $this->pluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        // mock date
        $date = '2014-12-18';
        $this->mockDate($date);

        // mock task REST calls
        $defaultTaskSearchParams = [
            'date'       => 'tdt_today',
            'status'     => 'tst_open',
            'sort'       => 'actionDate',
            'order'      => 'ASC',
            'page'       => 1,
            'limit'      => 10,
            'licenceId'  => $licenceId,
            'isClosed'   => false,
            'actionDate' => '<= 2014-12-18',
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
        $this->sm->setService('Helper\Rest', $restHelperMock);

        $dsm = m::mock('\StdClass')
            ->shouldReceive('get')
            ->with('Olcs\Service\Data\Cases')
            ->andReturn(
                m::mock('Olcs\Service\Data\Cases')
                    ->shouldReceive('fetchCaseData')
                    ->with($caseId)
                    ->andReturn(
                        [
                            'id' => $caseId,
                            'licence' => ['id' => $licenceId, 'licNo' => 'AB1234'],
                        ]
                    )
                    ->getMock()
            )
            ->getMock();
        $this->sm->setService('DataServiceManager', $dsm);

        // mock table service
        $this->sm->setService(
            'Table',
            m::mock('\Common\Service\Table\TableBuilder')
                ->shouldReceive('buildTable')
                ->andReturnSelf()
                ->shouldReceive('removeColumn')->twice()
                ->getMock()
        );

        $sut = new Sut;
        $sut->setPluginManager($this->pluginManager);
        $sut->setServiceLocator($this->sm);

        $view = $sut->indexAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
    }

    /**
     * Test index action with a POST
     * @group task
     */
    public function testIndexActionWithActionSubmitted()
    {
        $caseId = 28;
        $taskId = 456;
        $action = 'Close Task';
        $expectedRouteParams = [
            'action' => 'close',
            'type'   => 'case',
            'typeId' => $caseId,
            'task'   => $taskId,
        ];

        $sut = m::mock('\Olcs\Controller\Cases\Processing\TaskController')
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
            ->with('case')
            ->andReturn($caseId);

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
}
