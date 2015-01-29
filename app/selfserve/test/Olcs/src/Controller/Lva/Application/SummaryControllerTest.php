<?php

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\Controller\Lva\Application;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService;

/**
 * Summary Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryControllerTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = m::mock('\Olcs\Controller\Lva\Application\SummaryController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider indexActionProvider
     */
    public function testIndexAction($licenceCategory, $licenceType, $tmResults, $expectedWarningText, $expectedActions)
    {
        // Data
        $id = 3;
        $licenceId = 4;
        $licenceData = [
            'licNo' => 123456
        ];
        $tolData = [
            'goodsOrPsv' => $licenceCategory,
            'licenceType' => $licenceType
        ];
        $tmData = ['Results' => $tmResults];

        // Mocks
        $mockLicenceEntity = m::mock();
        $mockApplicationEntity = m::mock();
        $mockTmApplicationEntity = m::mock();

        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTmApplicationEntity);

        // Expectations
        $this->sut->shouldReceive('getIdentifier')
            ->andReturn($id)
            ->shouldReceive('getLicenceId')
            ->andReturn($licenceId);

        $mockLicenceEntity->shouldReceive('getById')
            ->with($licenceId)
            ->andReturn($licenceData);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($tolData);

        $mockTmApplicationEntity->shouldReceive('getByApplication')
            ->with($id)
            ->andReturn($tmData);

        $view = $this->sut->indexAction();
        $params = $view->getVariables();

        // Assertions
        $this->assertEquals('pages/application-summary', $view->getTemplate());
        $this->assertEquals(123456, $params['licence']);
        $this->assertEquals(3, $params['application']);
        $this->assertEquals($expectedWarningText, $params['warningText']);
        $this->assertEquals($expectedActions, $params['actions']);
    }

    /**
     * @dataProvider indexActionProvider
     */
    public function testPostSubmitSummaryAction(
        $licenceCategory,
        $licenceType,
        $tmResults,
        $expectedWarningText,
        $expectedActions
    ) {
        // Data
        $id = 3;
        $licenceId = 4;
        $licenceData = [
            'licNo' => 123456
        ];
        $tolData = [
            'goodsOrPsv' => $licenceCategory,
            'licenceType' => $licenceType
        ];
        $tmData = ['Results' => $tmResults];
        $summaryData = [
            'status' => [
                'description' => 'some status'
            ],
            'receivedDate' => '2014-01-01',
            'targetCompletionDate' => '2014-02-01'
        ];

        // Mocks
        $mockLicenceEntity = m::mock();
        $mockApplicationEntity = m::mock();
        $mockTmApplicationEntity = m::mock();

        $this->sm->setService('Entity\Licence', $mockLicenceEntity);
        $this->sm->setService('Entity\Application', $mockApplicationEntity);
        $this->sm->setService('Entity\TransportManagerApplication', $mockTmApplicationEntity);

        // Expectations
        $this->sut->shouldReceive('getIdentifier')
            ->andReturn($id)
            ->shouldReceive('getLicenceId')
            ->andReturn($licenceId);

        $mockLicenceEntity->shouldReceive('getById')
            ->with($licenceId)
            ->andReturn($licenceData);

        $mockApplicationEntity->shouldReceive('getTypeOfLicenceData')
            ->with($id)
            ->andReturn($tolData)
            ->shouldReceive('getSubmitSummaryData')
            ->with($id)
            ->andReturn($summaryData);

        $mockTmApplicationEntity->shouldReceive('getByApplication')
            ->with($id)
            ->andReturn($tmData);

        $view = $this->sut->postSubmitSummaryAction();
        $params = $view->getVariables();

        // Assertions
        $this->assertEquals('pages/application-post-submit-summary', $view->getTemplate());
        $this->assertEquals(123456, $params['licence']);
        $this->assertEquals(3, $params['application']);
        $this->assertEquals($expectedWarningText, $params['warningText']);
        $this->assertEquals($expectedActions, $params['actions']);
        $this->assertEquals('some status', $params['status']);
        $this->assertEquals('01 January 2014', $params['submittedDate']);
        $this->assertEquals('01 February 2014', $params['targetCompletionDate']);
    }

    public function indexActionProvider()
    {
        return [
            'GV, SN, No Tms' => [
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
                [],
                'markup-summary-warning-new-goods-application',
                ['markup-summary-application-actions-document']
            ],
            'GV, SN, With Tms' => [
                LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE,
                LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    ['foo' => 'bar']
                ],
                'markup-summary-warning-new-goods-application',
                [
                    'summary-application-actions-transport-managers',
                    'markup-summary-application-actions-document'
                ]
            ],
            'PSV, SN, With Tms' => [
                LicenceEntityService::LICENCE_CATEGORY_PSV,
                LicenceEntityService::LICENCE_TYPE_STANDARD_NATIONAL,
                [
                    ['foo' => 'bar']
                ],
                'markup-summary-warning-new-psv-application',
                [
                    'summary-application-actions-transport-managers',
                    'markup-summary-application-actions-document'
                ]
            ],
            'PSV, SR, With Tms' => [
                LicenceEntityService::LICENCE_CATEGORY_PSV,
                LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED,
                [
                    ['foo' => 'bar']
                ],
                'markup-summary-warning-new-psv-sr-application',
                [
                    'summary-application-actions-transport-managers',
                    'markup-summary-application-actions-document'
                ]
            ]
        ];
    }
}
