<?php
namespace OlcsTest\Controller;

use Olcs\Controller\Licence\Processing\LicenceProcessingPublicationsController;
use Mockery as m;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\TestHelpers\ControllerRouteMatchHelper;

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

        $mockLicenceData = [
            'cases' => [],
            'licNo' => $licenceNo,
            'status' => [
                'description' => $licenceDescription
            ],
            'organisation' => [
                'name' => $licenceOrganisation
            ]

        ];

        $listParams = [
            'page' => 1,
            'limit' => 10,
            'sort' => 'createdOn',
            'order' => 'DESC',
            'licence' => $licenceId
        ];

        $action = null;
        $defaultPage = 1;
        $defaultSort = 'createdOn';
        $defaultOrder = 'DESC';
        $defaultLimit = 10;
        $mockListData = ['Results' => [0 => ['id' => 1]], 'Count' => 1];

        //mock plugin manager
        $mockPluginManager = $this->pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'DataServiceManager' => 'DataServiceManager',
                'url' => 'url'
            ]
        );

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

        //mock service manager
        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Table')->andReturn($mockTableBuilder);
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturn($mockDataServiceManager);
        $mockServiceManager->shouldReceive('get')
            ->with('Common\Service\Data\Licence')
            ->andReturn($mockLicenceService);

        $this->sut->setServiceLocator($mockServiceManager);

        $this->sut->indexAction();
    }
}
