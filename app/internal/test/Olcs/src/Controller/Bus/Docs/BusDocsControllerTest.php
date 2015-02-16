<?php

/**
 * Bus Docs Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Bus\Docs;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Olcs\Controller\Bus\Docs\BusDocsController;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Mockery as m;
use OlcsTest\Bootstrap;

/**
 * Bus Docs Controller Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDocsControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        parent::setUp();
    }

    public function testDocumentsAction()
    {
        $busRegId  = 69;
        $licenceId = 7;

        $sut = m::mock('Olcs\Controller\Bus\Docs\BusDocsController')
            ->makePartial()->shouldAllowMockingProtectedMethods();

        // Mock params
        $sut->shouldReceive('getFromRoute')->with('busRegId')->andReturn($busRegId);
        $sut->shouldReceive('getFromRoute')->with('licence')->andReturn($licenceId);

        $sm = Bootstrap::getServiceManager();

        $service = m::mock('Common\Service\Data\BusReg');
        $service->shouldReceive('fetchOne')->with($busRegId);

        $pluginManager = m::mock('Common\Service\Data\PluginManager');
        $pluginManager->shouldReceive('get')->with('Common\Service\Data\BusReg')->andReturn($service);

        $sm->setService('DataServiceManager', $pluginManager);

        // Mock/stub all the service calls that generate the table content
        $tableServiceMock = m::mock('\Common\Service\Table\TableBuilder')
            ->shouldReceive('buildTable')
            ->andReturnSelf()
            ->shouldReceive('render')
            ->getMock();
        $sm->setService('Table', $tableServiceMock);

        // Mock script helper
        $scriptHelperMock = m::mock('\Common\Service\Script\ScriptFactory')
            ->shouldReceive('loadFiles')
            ->with(['documents', 'table-actions'])
            ->getMock();
        $sm->setService('Script', $scriptHelperMock);

        // Mock document REST calls and the BusReg lookup
        $restHelperMock = $this->getMockRestHelperForDocuments();
        $restHelperMock->shouldReceive('makeRestCall')
            ->with(
                'BusReg',
                'GET',
                [
                    'id' => $busRegId,
                    'bundle' => '{"children":{"licence":{"properties":"ALL","children":["organisation"]},'
                        . '"status":{"properties":"ALL"},'
                        . '"withdrawnReason":{"properties":"ALL"}}}'
                ],
                m::any()
            );
        $sm->setService('Helper\Rest', $restHelperMock);

        $nav = m::mock('\Zend\Navigation\Navigation')
            ->shouldReceive('findOneBy')
            ->with('id', 'licence_bus_docs')
            ->getMock();
        $sm->setService('Navigation', $nav);

        $sut->shouldReceive('getForm')->with('documents-home')->andReturn(
            m::mock()
                ->shouldReceive('get')->andReturn(
                    m::mock()->shouldReceive('setValueOptions')->getMock()
                )
                ->shouldReceive('remove')
                ->shouldReceive('setData')
                ->getMock()
        );

        $sut->setServiceLocator($sm);

        $view = $sut->documentsAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
    }

    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
        $busRegId  = 69;
        $licenceId = 7;

        $sut = m::mock('Olcs\Controller\Bus\Docs\BusDocsController')->makePartial();

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
                    ->andReturn('upload')
                    ->getMock()
            );

        $sut->shouldReceive('getFromRoute')
            ->with('busRegId')
            ->andReturn($busRegId)
            ->shouldReceive('getFromRoute')
            ->with('licence')
            ->andReturn($licenceId);

        $sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRoute')
                    ->with(
                        'licence/bus-docs/upload',
                        ['busRegId' => 69, 'licence' => 7]
                    )
                    ->andReturn('thisistheredirect')
                    ->getMock()
            );

        $response = $sut->documentsAction();

        $this->assertEquals('thisistheredirect', $response);
    }

    protected function getMockRestHelperForDocuments()
    {
        return m::mock('Common\Service\Helper\RestHelperService')
            ->shouldReceive('makeRestCall')
            ->with(
                'DocumentSearchView',
                'GET',
                [
                    'sort' => "issuedDate",
                    'order' => "DESC",
                    'page' => 1,
                    'limit' => 10,
                    'licenceId' => 7
                ],
                m::any() // last param is usually a blank bundle specifier
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'Category',
                'GET',
                [
                    'limit'         => 100,
                    'sort'          => 'description',
                    'isDocCategory' => true,
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'SubCategory',
                'GET',
                [
                    'sort'      => 'subCategoryName',
                    'order'     => 'ASC',
                    'page'      => 1,
                    'limit'     => 100,
                    'licenceId' => 7,
                    'isDoc'     => true
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'RefData',
                'GET',
                [
                    'refDataCategoryId' => 'document_type',
                    'limit'=>100,
                    'sort'=>'description'
                ],
                m::any()
            )
            ->getMock();
    }
}
