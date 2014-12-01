<?php
/**
 * TransportManagerCaseControllerTest
*
* @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
*/
namespace OlcsTest\Controller\Document;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\Controller\TransportManager\TransportManagerCaseController as Sut;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;

/**
 * TransportManagerCaseControllerTest
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class TransportManagerCaseControllerTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public function testIndexAction()
    {
        $serviceName = 'Olcs\Service\Data\Cases';
        $results = ['id' => '1'];

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(['params' => 'Params', 'url' => 'Url']);

        $params = [
            'transportManager' => 1,
            'page'    => 1,
            'sort'    => 'id',
            'order'   => 'desc',
            'limit'   => 10,
            'url'     => $mockPluginManager->get('url')
        ];

        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('transportManager', '')->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('page', 1)->andReturn(1);
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('sort', 'id')->andReturn('id');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('order', 'desc')->andReturn('desc');
        $mockPluginManager->get('params', '')->shouldReceive('fromRoute')->with('limit', 10)->andReturn(10);

        $mockPluginManager->get('params', '')->shouldReceive('fromPost')->with('action')->andReturn(null);

        $dataService = m::mock($serviceName);
        $dataService->shouldReceive('fetchList')->andReturn($results);

        $serviceLocator = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $serviceLocator->shouldReceive('get')->with($serviceName)->andReturn($dataService);

        $tableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $tableBuilder->shouldReceive('buildTable')->with('case', $results, $params, false)->andReturn('tableContent');

        $serviceLocator->shouldReceive('get')->with('Table')->andReturn($tableBuilder);

        $sut = new Sut;
        $sut->setSearchForm('true'); // anything that's notnull.
        $sut->setPluginManager($mockPluginManager);
        $sut->setServiceLocator($serviceLocator);

        $sut->indexAction();
    }
}