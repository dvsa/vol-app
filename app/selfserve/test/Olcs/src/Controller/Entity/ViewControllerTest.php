<?php

namespace OlcsTest\Controller;

use Common\RefData;
use Doctrine\DBAL\Schema\View;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Entity\ViewController;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use ZfcRbac\Mvc\Controller\Plugin\IsGranted;

/**
 * Entity View Controller Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ViewControllerTest extends MockeryTestCase
{
    /** @var  ViewController|m\MockInterface */
    private $sut;
    /** @var  \Zend\ServiceManager\ServiceLocatorInterface|m\MockInterface */
    private $mockSl;
    /** @var  \Zend\Mvc\Controller\PluginManager|m\MockInterface */
    private $mockPluginManager;
    /** @var  m\MockInterface */
    private $mockIsGrantedPlgn;

    public function setUp()
    {
        $this->mockSl = m::mock(\Zend\ServiceManager\ServiceLocatorInterface::class)->makePartial();
        $this->mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();

        //  mock plugins
        $this->mockPluginManager = (new ControllerPluginManagerHelper)->getMockPluginManager(
            [
                'handleQuery' => 'handleQuery',
                'url' => 'Url',
                'params' => 'Params',
            ]
        );

        $urlPlugin = $this->mockPluginManager->get('url', '');
        $urlPlugin->shouldReceive('fromRoute')->andReturn('foo');

        $this->mockIsGrantedPlgn = m::mock(IsGranted::class);
        $this->mockPluginManager
            ->shouldReceive('get')
            ->with('isGranted', null)
            ->andReturn($this->mockIsGrantedPlgn);

        //  instance of tested class
        $this->sut = new ViewController();
        $this->sut->setServiceLocator($this->mockSl);
        $this->sut->setPluginManager($this->mockPluginManager);
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * Tests index details action for licence entity Non Partner
     */
    public function testIndexActionNonPartner()
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
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery', '');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        //  mock table
        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-anonymous', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-anonymous', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        $this->mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
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
        static::assertEquals($content->operatingCentresTable, 'operatingCentresTableResult');
    }

    /**
     * Tests index details action for licence entity for Partner
     */
    public function testIndexActionPartner()
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
        ];

        //  mock plugins
        $mockQueryHandler = $this->mockPluginManager->get('handleQuery', '');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $this->mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        //  mock table
        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-operating-centres-partner', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-oppositions-partner', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult2');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-vehicles-partner', $mockResult['vehicles'])
            ->andReturn('vehiclesTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-current-applications-partner', $mockResult['currentApplications'])
            ->andReturn('currentApplicationsTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('entity-view-conditions-undertakings-partner', $mockResult['conditionUndertakings'])
            ->andReturn('conditionsUndertakingsTableResult');

        $this->mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);

        //  mock permissions
        $this->mockIsGrantedPlgn->shouldReceive('__invoke')
            ->with(anyOf([RefData::PERMISSION_SELFSERVE_PARTNER_ADMIN, RefData::PERMISSION_SELFSERVE_PARTNER_USER]))
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
        static::assertEquals($content->operatingCentresTable, 'operatingCentresTableResult');
        static::assertEquals($content->vehiclesTable, 'vehiclesTableResult');
        static::assertEquals($content->currentApplicationsTable, 'currentApplicationsTableResult');
        static::assertEquals($content->conditionsUndertakingsTable, 'conditionsUndertakingsTableResult');
    }
}
