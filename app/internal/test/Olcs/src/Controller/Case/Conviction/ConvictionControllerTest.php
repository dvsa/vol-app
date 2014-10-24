<?php
namespace OlcsTest\Controller\Conviction;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\Http\Request;
use Zend\Http\Response;
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
        $this->event      = new MvcEvent();
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($routeMatch);
        $this->sut->setEvent($this->event);

        parent::setUp();
    }

    public function testSaveDefendantTypeOperator()
    {

        $service = 'Conviction';
        $data = [
            'id' => 1,
            'defendantType' => 'def_t_op',
            'licence' => [
                'organisation' => [
                    'name' => 'some operator'
                ]
            ]
        ];

        $mockRestHelper = m::mock('RestHelper');
        $caseId = 1;
        $case = [
            'id' => 99,
            'licence' => [
                'organisation' => [
                    'name' => 'some operator'
                ]
            ]
        ];

        // get case
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Cases',
            'GET',
            array('id' => $caseId),
            m::type('array')
        )->andReturn($case);

        // save conviction
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Conviction',
            'PUT',
            m::type('array'),
            ""
        );

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('HelperService')->andReturnSelf();
        $mockServiceManager->shouldReceive('getHelperService')->with('RestHelper')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get->getHelperService')->with('RestService')->andReturn($mockRestHelper);

        $this->sut->getEvent()->getRouteMatch()->setParam('case', $caseId);

        $this->sut->setServiceLocator($mockServiceManager);

        $result = $this->sut->save($data, $service);

        $this->assertNull($result);
    }
}
