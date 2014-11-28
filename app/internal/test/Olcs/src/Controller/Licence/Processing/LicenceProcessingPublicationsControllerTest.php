<?php
namespace OlcsTest\Controller\Licence\Processing;

use Zend\Form\Annotation\AnnotationBuilder;
use Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Zend\Form\Form;

/**
 * Class LicenceProcessingPublicationsControllerTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingPublicationsControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerPluginManagerHelper
     */
    protected $pluginManagerHelper;

    /**
     * @var ControllerRouteMatchHelper
     */
    protected $routeMatchHelper;

    public function setUp()
    {
        $this->sut = new LicenceProcessingPublicationsController();
        $this->pluginManagerHelper = new ControllerPluginManagerHelper();
        $this->routeMatchHelper = new ControllerRouteMatchHelper();

        parent::setUp();
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {
        $licenceId = 7;
        $licenceDescription = 'Licence desc';
        $licenceOrganisation = 'Organisation name';
        $licenceNo = 'OB123456';

        $form = new Form();

        $mockLicenceData = [
            'id' => $licenceId,
            'cases' => [],
            'licNo' => $licenceNo,
            'goodsOrPsv' => [
                'id' => 'lcat_psv'
            ],
            'status' => [
                'description' => $licenceDescription
            ],
            'organisation' => [
                'name' => $licenceOrganisation
            ]

        ];

        $action = null;
        $defaultPage = 1;
        $defaultSort = 'createdOn';
        $defaultOrder = 'DESC';
        $defaultLimit = 10;

        $listParams = [
            'page' => $defaultPage,
            'limit' => $defaultLimit,
            'sort' => $defaultSort,
            'order' => $defaultOrder,
            'licence' => $licenceId
        ];

        $mockListData = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        $event = $this->routeMatchHelper->getMockRouteMatch(
            array(
                'controller' => 'publications',
                'action' => null,
                'licence' => $licenceId
            )
        );

        $this->sut->setEvent($event);

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'DataServiceManager' => 'DataServiceManager',
                'url' => 'url'
            ]
        );

        //mock url helper
        $mockPluginManager->shouldReceive('get')->with('url')->andReturnSelf();

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('fetchList')->with($listParams)->andReturn($mockListData);

        //data service manager
        $mockDataServiceManager = $mockPluginManager->get('DataServiceManager', '');
        $mockDataServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('licence')->andReturn($licenceId);

        $mockParams->shouldReceive('fromPost')->with('action')->andReturn($action);
        $this->sut->setPluginManager($mockPluginManager);

        $mockLicenceService = m::mock('Common\Service\Data\Licence');
        $mockLicenceService->shouldReceive('fetchLicenceData')->with($licenceId)->andReturn($mockLicenceData);

        //mock table builder
        $mockTableBuilder = m::mock('Common\Service\Table\TableBuilder');
        $mockTableBuilder->shouldReceive('buildTable')->withAnyArgs();

        $mockRouter = m::mock('Zend\Mvc\Router\Http\TreeRouteStack');

        $formAnnotationBuilder = new AnnotationBuilder();

        //mock form helper
        $mockFormHelper = m::mock('Helper\Form');
        $mockFormHelper->shouldReceive('createForm')->andReturn($form);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturn($mockDataServiceManager);
        $mockServiceManager->shouldReceive('get')->with('router')->andReturn($mockRouter);
        $mockServiceManager->shouldReceive('get')->with('FormAnnotationBuilder')->andReturn($formAnnotationBuilder);
        $mockServiceManager->shouldReceive('get')->with('Helper\Form')->andReturn($mockFormHelper);
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\Licence')
            ->andReturn($mockLicenceService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Tests the delete action
     */
    public function testDeleteAction()
    {
        $id = 1;

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');
        $mockPublicationLink->shouldReceive('delete')->with($id)->andReturn([]);

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);

        //flash messenger
        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addErrorMessage')->once();

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->andReturn('redirectResponse');

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    /**
     * @dataProvider serviceAndResourceNotFoundProvider
     *
     * @param $expectedException
     * @param $message
     */
    public function testServiceAndResourceNotFoundExceptions($expectedException, $message)
    {
        $id = 1;

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'FlashMessenger' => 'FlashMessenger',
                'redirect' => 'Redirect'
            ]
        );

        //publication link service
        $mockPublicationLink = m::mock('Common\Service\Data\PublicationLink');

        //publication link shouldn't try to delete
        $class = 'Common\Exception\\' . $expectedException;
        $mockPublicationLink->shouldReceive('delete')->andThrow(new $class($message));

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\PublicationLink')
            ->andReturn($mockPublicationLink);

        //route params
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('id')->andReturn($id);

        //flash messenger
        $mockFlashMessenger = $mockPluginManager->get('FlashMessenger', '');
        $mockFlashMessenger->shouldReceive('addErrorMessage')->with($message)->once();

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->andReturn('redirectResponse');

        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $this->sut->setPluginManager($mockPluginManager);
        $this->sut->setServiceLocator($mockServiceManager);

        $this->assertEquals('redirectResponse', $this->sut->deleteAction());
    }

    public function serviceAndResourceNotFoundProvider()
    {
        return [
            [
                'DataServiceException',
                'Error message'
            ],
            [
                'ResourceNotFoundException',
                'Error message'
            ],
            [
                'BadRequestException',
                'Error message'
            ]
        ];
    }
}
