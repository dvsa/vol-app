<?php
/**
 * Class User Controller Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Olcs\Controller\UserController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class User Controller Test
 *
 * @package OlcsTest\Controller
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserControllerTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testIndexAction()
    {
        $page = '2';
        $sort = 'name';
        $order = 'ASC';
        $limit = 20;
        $query = [];

        $paramsArr = [
            'page'    => $page,
            'sort'    => $sort,
            'order'   => $order,
            'limit'   => $limit,
            'query'   => $query
        ];

        $table = ['table'];

        $sut = new UserController();

        $data = ['data'];

        $params = m::mock('\Zend\Mvc\Controller\Plugin\Params');
        $params->shouldReceive('fromQuery')->with('page', 1)->andReturn($page);
        $params->shouldReceive('fromQuery')->with('sort', 'id')->andReturn($sort);
        $params->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn($order);
        $params->shouldReceive('fromQuery')->with('limit', 10)->andReturn($limit);
        $params->shouldReceive('fromQuery')->withNoArgs()->andReturn($query);

        $pm = m::mock('\Zend\Mvc\Controller\PluginManager');
        $pm->shouldReceive('get')->with('params')->andReturn($params);
        $pm->shouldReceive('setController')->with($sut);

        $service = m::mock('stdClass');
        $service->shouldReceive('getList')->with($paramsArr)->andReturn($data);

        $url = m::mock('Common\Service\Helper\UrlHelperService');
        $paramsArr['url'] = $url;

        $table = m::mock('Common\Service\Table\TableBuilder');
        $table->shouldReceive('buildTable')->with('users', $data, $paramsArr, false)->andReturn($table);

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $sl->shouldReceive('get')->with('Entity\User')->andReturn($service);
        $sl->shouldReceive('get')->with('Helper\Url')->andReturn($url);
        $sl->shouldReceive('get')->with('Table')->andReturn($table);

        $sut->setPluginManager($pm);
        $sut->setServiceLocator($sl);

        $view = $sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\User', $view);
        $this->assertEquals($table, $view->getVariable('users'));
    }
}