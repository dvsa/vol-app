<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableBuilder;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Entity\ViewController;
use OlcsTest\TestHelpers\ControllerPluginManagerHelper;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Mvc\Controller\Plugin\IsGranted;

/**
 * Entity View Controller Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ViewControllerTest extends MockeryTestCase
{
    /**
     * @var (\Dvsa\Olcs\Utils\Translation\NiTextTranslation & \Mockery\MockInterface)
     */
    public $mockNiUtil;
    /**
     * @var (\LmcRbacMvc\Service\AuthorizationService & \Mockery\MockInterface)
     */
    public $mockAuth;
    /**
     * @var (\Common\Service\Helper\FlashMessengerHelperService & \Mockery\MockInterface)
     */
    public $mockFlash;
    public $mockTable;
    /** @var  ViewController|m\MockInterface */
    private $sut;
    /** @var  \Laminas\ServiceManager\ServiceLocatorInterface|m\MockInterface */
    private $mockSl;
    /** @var  \Laminas\Mvc\Controller\PluginManager|m\MockInterface */
    private $mockPluginManager;
    /** @var  m\MockInterface */
    private $mockIsGrantedPlgn;

    public function setUp(): void
    {
        //  mock plugins
        $this->mockPluginManager = (new ControllerPluginManagerHelper())->getMockPluginManager(
            [
                'handleQuery' => 'handleQuery',
                'url' => 'Url',
                'params' => 'Params',
            ]
        );

        $urlPlugin = $this->mockPluginManager->get('url');
        $urlPlugin->shouldReceive('fromRoute')->andReturn('foo');

        $this->mockIsGrantedPlgn = m::mock(IsGranted::class);
        $this->mockPluginManager
            ->shouldReceive('get')
            ->with('isGranted', null)
            ->andReturn($this->mockIsGrantedPlgn);

        //  instance of tested class
        $this->mockNiUtil = m::mock(NiTextTranslation::class);
        $this->mockAuth = m::mock(\LmcRbacMvc\Service\AuthorizationService::class);
        $this->mockFlash = m::mock(FlashMessengerHelperService::class);
        $this->mockTable = m::mock(TableFactory::class);
        $this->sut = new ViewController(
            $this->mockNiUtil,
            $this->mockAuth,
            $this->mockFlash,
            $this->mockTable
        );

        $this->sut->setPluginManager($this->mockPluginManager);
    }

    public function tearDown(): void
    {
        m::close();
    }

    /**
     * Tests index details action for licence entity Non Partner, mixed with lgv
     */
    public function testIndexActionNonPartnerMixedWithLgv(): void
    {
        $entity = 'licence';
        $entityId = 7;

        $mockResult = [
            'organisation' => [
                'name' => 'MYCOMPANY',
                'companyOrLlpNo' => '12345'
            ],
            'licNo' => 'OB12345',
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => [],
            'status' => ['id' => 'foo'],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        //  mock table
        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $operatingCentresColumns = [
            'noOfVehiclesRequired' => [
                'title' => [
                    'translation-key-vehicles',
                ]
            ]
        ];

        $expectedUpdatedOperatingCentresColumns = [
            'noOfVehiclesRequired' => [
                'title' => [
                    'translation-key-hgvs',
                ]
            ]
        ];

        $mockOperatingCentresTableBuilder = m::mock(TableBuilder::class);
        $mockOperatingCentresTableBuilder->shouldReceive('getColumns')
            ->withNoArgs()
            ->andReturn($operatingCentresColumns);
        $mockOperatingCentresTableBuilder->shouldReceive('setColumns')
            ->with($expectedUpdatedOperatingCentresColumns)
            ->once();

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-anonymous', $mockResult['operatingCentres'], [], false)
            ->andReturn($mockOperatingCentresTableBuilder);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-anonymous', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(\Hamcrest\Matchers::anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
            ->andReturn(false);

        //  call & check
        /** @var ViewModel $result */
        $result = $this->sut->detailsAction();
        $children = $result->getChildren();

        $content = reset($children);

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertInstanceOf(ViewModel::class, $content);

        static::assertEquals($result->pageTitle, 'MYCOMPANY');
        static::assertEquals($result->pageSubtitle, 'OB12345');
        static::assertEquals($result->userType, ViewController::USER_TYPE_ANONYMOUS);
        static::assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        static::assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        static::assertEquals($content->operatingCentresTable, $mockOperatingCentresTableBuilder);
    }

    /**
     * Tests index details action for licence entity Non Partner, mixed without lgv
     */
    public function testIndexActionNonPartnerMixedWithoutLgv(): void
    {
        $entity = 'licence';
        $entityId = 7;

        $mockResult = [
            'organisation' => [
                'name' => 'MYCOMPANY',
                'companyOrLlpNo' => '12345'
            ],
            'licNo' => 'OB12345',
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => [],
            'status' => ['id' => 'foo'],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => null,
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        //  mock table

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockOperatingCentresTableBuilder = m::mock(TableBuilder::class);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-anonymous', $mockResult['operatingCentres'], [], false)
            ->andReturn($mockOperatingCentresTableBuilder);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-anonymous', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(\Hamcrest\Matchers::anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
            ->andReturn(false);

        //  call & check
        /** @var ViewModel $result */
        $result = $this->sut->detailsAction();
        $children = $result->getChildren();

        $content = reset($children);

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertInstanceOf(ViewModel::class, $content);

        static::assertEquals($result->pageTitle, 'MYCOMPANY');
        static::assertEquals($result->pageSubtitle, 'OB12345');
        static::assertEquals($result->userType, ViewController::USER_TYPE_ANONYMOUS);
        static::assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        static::assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        static::assertEquals($content->operatingCentresTable, $mockOperatingCentresTableBuilder);
    }

    /**
     * Tests index details action for licence entity for Partner, mixed with lgv
     */
    public function testIndexActionPartnerMixedWithLgv(): void
    {
        $entity = 'licence';
        $entityId = 7;

        $mockResult = [
            'organisation' => [
                'name' => 'MYCOMPANY',
                'companyOrLlpNo' => '12345'
            ],
            'licNo' => 'OB12345',
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => [],
            'vehicles' => [],
            'currentApplications' => [],
            'conditionUndertakings' => [],
            'applications' => [],
            'status' => ['id' => 'foo'],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => 0,
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $operatingCentresColumns = [
            'noOfVehiclesRequired' => [
                'title' => [
                    'translation-key-vehicles',
                ]
            ]
        ];

        $expectedUpdatedOperatingCentresColumns = [
            'noOfVehiclesRequired' => [
                'title' => [
                    'translation-key-hgvs',
                ]
            ]
        ];

        $mockOperatingCentresTableBuilder = m::mock(TableBuilder::class);
        $mockOperatingCentresTableBuilder->shouldReceive('getColumns')
            ->withNoArgs()
            ->andReturn($operatingCentresColumns);
        $mockOperatingCentresTableBuilder->shouldReceive('setColumns')
            ->with($expectedUpdatedOperatingCentresColumns)
            ->once();

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-partner', $mockResult['operatingCentres'], [], false)
            ->andReturn($mockOperatingCentresTableBuilder);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-partner', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-vehicles-partner', $mockResult['vehicles'])
            ->andReturn('vehiclesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-current-applications-partner', $mockResult['currentApplications'])
            ->andReturn('currentApplicationsTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-conditions-undertakings-partner', $mockResult['conditionUndertakings'])
            ->andReturn('conditionsUndertakingsTableResult');

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(\Hamcrest\Matchers::anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
            ->andReturn(true);

        //  call & check
        /** @var ViewModel $result */
        $result = $this->sut->detailsAction();
        $children = $result->getChildren();

        $content = reset($children);

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertInstanceOf(ViewModel::class, $content);

        static::assertEquals($result->pageTitle, 'MYCOMPANY');
        static::assertEquals($result->pageSubtitle, 'OB12345');
        static::assertEquals($result->userType, ViewController::USER_TYPE_PARTNER);
        static::assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        static::assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        static::assertEquals($content->operatingCentresTable, $mockOperatingCentresTableBuilder);
        static::assertEquals($content->vehiclesTable, 'vehiclesTableResult');
        static::assertEquals($content->currentApplicationsTable, 'currentApplicationsTableResult');
        static::assertEquals($content->conditionsUndertakingsTable, 'conditionsUndertakingsTableResult');
    }

    /**
     * Tests index details action for licence entity for Partner, mixed without lgv
     */
    public function testIndexActionPartnerMixedWithoutLgv(): void
    {
        $entity = 'licence';
        $entityId = 7;

        $mockResult = [
            'organisation' => [
                'name' => 'MYCOMPANY',
                'companyOrLlpNo' => '12345'
            ],
            'licNo' => 'OB12345',
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => [],
            'vehicles' => [],
            'currentApplications' => [],
            'conditionUndertakings' => [],
            'applications' => [],
            'status' => ['id' => 'foo'],
            'vehicleType' => ['id' => RefData::APP_VEHICLE_TYPE_MIXED],
            'totAuthLgvVehicles' => null,
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        //  mock table
        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockOperatingCentresTableBuilder = m::mock(TableBuilder::class);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-partner', $mockResult['operatingCentres'], [], false)
            ->andReturn($mockOperatingCentresTableBuilder);

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-partner', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-vehicles-partner', $mockResult['vehicles'])
            ->andReturn('vehiclesTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-current-applications-partner', $mockResult['currentApplications'])
            ->andReturn('currentApplicationsTableResult');

        $this->mockTable->shouldReceive('buildTable')
            ->with('entity-view-conditions-undertakings-partner', $mockResult['conditionUndertakings'])
            ->andReturn('conditionsUndertakingsTableResult');

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(\Hamcrest\Matchers::anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
            ->andReturn(true);

        //  call & check
        /** @var ViewModel $result */
        $result = $this->sut->detailsAction();
        $children = $result->getChildren();

        $content = reset($children);

        static::assertInstanceOf(ViewModel::class, $result);
        static::assertInstanceOf(ViewModel::class, $content);

        static::assertEquals($result->pageTitle, 'MYCOMPANY');
        static::assertEquals($result->pageSubtitle, 'OB12345');
        static::assertEquals($result->userType, ViewController::USER_TYPE_PARTNER);
        static::assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        static::assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        static::assertEquals($content->operatingCentresTable, $mockOperatingCentresTableBuilder);
        static::assertEquals($content->vehiclesTable, 'vehiclesTableResult');
        static::assertEquals($content->currentApplicationsTable, 'currentApplicationsTableResult');
        static::assertEquals($content->conditionsUndertakingsTable, 'conditionsUndertakingsTableResult');
    }
}
