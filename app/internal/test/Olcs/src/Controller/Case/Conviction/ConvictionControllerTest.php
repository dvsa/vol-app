<?php
namespace OlcsTest\Controller\Conviction;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;

/**
 * Conviction controller form post tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConvictionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->sut = new \Olcs\Controller\Cases\Conviction\ConvictionController();

        $routeMatch = new RouteMatch(array('controller' => 'conviction'));
        $this->event = new MvcEvent();
        $routerConfig = array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($routeMatch);
        $this->sut->setEvent($this->event);

        parent::setUp();
    }

    /**
     * @dataProvider saveDefendantTypeOperatorProvider
     */
    public function testSaveDefendantTypeOperator($data)
    {
        $service = 'Conviction';

        $caseId = 1;
        $case = [
            'id' => 99,
            'licence' => [
                'organisation' => [
                    'name' => 'some operator'
                ]
            ]
        ];

        $mockRestHelper = m::mock('RestHelper');
        // save conviction
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Conviction',
            'PUT',
            m::type('array'),
            ""
        );

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($case);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $this->sut->getEvent()->getRouteMatch()->setParam('case', $caseId);

        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->save($data, $service);

        $this->assertNull($result);
    }

    public function saveDefendantTypeOperatorProvider()
    {
        return [
            [[
                'id' => 1,
                'defendantType' => 'def_t_op',
                'licence' => [
                    'organisation' => [
                        'name' => 'some operator'
                    ]
                ]
            ]],
            [[
                'id' => 1,
                'defendantType' => 'def_t_driver'
            ]]
        ];
    }
}
