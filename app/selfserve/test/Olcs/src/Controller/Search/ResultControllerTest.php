<?php

/**
 * Search Result Controller Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\TestHelpers\ControllerPluginManagerHelper;
use Olcs\Controller\Search\ResultController;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Search Result Controller Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ResultControllerTest extends MockeryTestCase
{
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
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => []
        ];

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'handleQuery' => 'handleQuery',
                'url' => 'Url',
                'params' => 'Params'
            ]
        );

        // Mock the auth service
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->matchArgs(['partner-admin','partner-user'])
            ->andReturn(false);

        $mockQueryHandler = $mockPluginManager->get('handleQuery', '');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('/search-results/related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('/search-results/transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('/search-results/operating-centres', $mockResult['operatingCentres'])
            ->andReturn('operatingCentresTableResult');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);

        $sut = new ResultController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();
        $children = $result->getChildren();
        $content = $children[0];
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $content);

        $this->assertEquals($result->pageTitle, 'MYCOMPANY');
        $this->assertEquals($result->pageSubtitle, '12345');
        $this->assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        $this->assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        $this->assertEquals($content->operatingCentresTable, 'operatingCentresTableResult');
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
            'otherLicences' => [],
            'transportManagers' => [],
            'operatingCentres' => []
        ];

        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'handleQuery' => 'handleQuery',
                'url' => 'Url',
                'params' => 'Params'
            ]
        );

        // Mock the auth service
        $mockAuthService = m::mock();
        $mockAuthService->shouldReceive('isGranted')
            ->matchArgs(['partner-admin','partner-user'])
            ->andReturn(true);

        $mockQueryHandler = $mockPluginManager->get('handleQuery', '');
        $mockQueryHandler->shouldReceive('isNotFound')->andReturn(false);
        $mockQueryHandler->shouldReceive('isClientError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isServerError')->andReturn(false);
        $mockQueryHandler->shouldReceive('isOk')->andReturn(true);
        $mockQueryHandler->shouldReceive('getResult')->andReturn($mockResult);

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('entity')->andReturn($entity);
        $mockParams->shouldReceive('fromRoute')->with('entityId')->andReturn($entityId);

        $mockTable = m::mock('Common\Service\Table\TableBuilder');
        $mockTable->shouldReceive('buildTable')
            ->with('/search-results/related-operator-licences', $mockResult['otherLicences'])
            ->andReturn('otherLicencesTableResult');

        $mockTable->shouldReceive('buildTable')
            ->with('/search-results/transport-managers', $mockResult['transportManagers'])
            ->andReturn('transportManagersTableResult');

        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $mockSl->shouldReceive('get')->with('ZfcRbac\Service\AuthorizationService')->andReturn($mockAuthService);

        $sut = new ResultController();
        $sut->setServiceLocator($mockSl);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->detailsAction();
        $children = $result->getChildren();
        $content = $children[0];
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $result);
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $content);

        $this->assertEquals($result->pageTitle, 'MYCOMPANY');
        $this->assertEquals($result->pageSubtitle, '12345');
        $this->assertEquals($content->relatedOperatorLicencesTable, 'otherLicencesTableResult');
        $this->assertEquals($content->transportManagerTable, 'transportManagersTableResult');
        $this->assertNull($content->operatingCentresTable);
    }
}
