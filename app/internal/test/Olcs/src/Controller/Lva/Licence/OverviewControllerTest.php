<?php

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Lva\Licence;

use Mockery as m;
use OlcsTest\Controller\Lva\AbstractLvaControllerTestCase;

use Common\Service\Entity\LicenceEntityService as Licence;
use Common\Service\Entity\ApplicationEntityService as Application;

/**
 * Internal Licencing Overview Controller Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewControllerTest extends AbstractLvaControllerTestCase
{
    private $overViewData1 = [
        'id'           => 123,
        'licNo'        => 'OB1234567',
        'version'      => 1,
        'reviewDate'   => '2016-05-04',
        'expiryDate'   => '2017-06-05',
        'inForceDate'  => '2014-03-02',
        'licenceType'  => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
        'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
        'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
        'totAuthVehicles' => 10,
        'totAuthTrailers' => 8,
        'totCommunityLicences' => null,
        'organisation' => [
            'id' => 72,
            'name' => 'John Smith Haulage',
            'tradingNames' => [
                ['name' => 'JSH Logistics'],
            ],
            'licences' => [
                ['id' => 210],
                ['id' => 208],
                ['id' => 203],
            ],
            'leadTcArea' => ['id' => 'B'],
        ],
        'applications' => [
            ['id' => 91],
            ['id' => 92],
            ['id' => 93],
            ['id' => 94],
        ],
        'licenceVehicles' => [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ],
        'operatingCentres' => [
            ['id' => 1],
            ['id' => 2],
        ],
    ];

    private $overViewData2 = [
        'id'           => 123,
        'licNo'        => 'PD2737280',
        'version'      => 1,
        'reviewDate'   => '2016-05-04',
        'expiryDate'   => '2017-06-05',
        'inForceDate'  => '2014-03-02',
        'surrenderedDate' => '2015-02-11',
        'licenceType'  => ['id' => Licence::LICENCE_TYPE_RESTRICTED],
        'status'       => ['id' => Licence::LICENCE_STATUS_SURRENDERED],
        'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_PSV],
        'totAuthVehicles' => 10,
        'totAuthTrailers' => 0,
        'totCommunityLicences' => 7,
        'psvDiscs' => [
            ['id' => 69],
            ['id' => 70],
            ['id' => 71],
            ['id' => 72],
            ['id' => 73],
            ['id' => 74],
        ],
        'organisation' => [
            'id' => 72,
            'name' => 'John Smith Coaches',
            'tradingNames' => [
                ['name' => 'JSC Express'],
            ],
            'licences' => [
                ['id' => 210],
                ['id' => 208],
                ['id' => 203],
            ],
            'leadTcArea' => ['id' => 'B'],
        ],
        'applications' => [
            ['id' => 91],
            ['id' => 92],
            ['id' => 93],
            ['id' => 94],
        ],
        'licenceVehicles' => [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ],
        'operatingCentres' => [
            ['id' => 1],
            ['id' => 2],
        ],
    ];

    private $overViewData3 = [
        'id'           => 123,
        'licNo'        => 'PD2737280',
        'version'      => 1,
        'reviewDate'   => '2016-05-04',
        'expiryDate'   => '2017-06-05',
        'inForceDate'  => '2014-03-02',
        'surrenderedDate' => '2015-02-11',
        'licenceType'  => ['id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
        'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
        'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_PSV],
        'totAuthVehicles' => 2,
        'totAuthTrailers' => 0,
        'totCommunityLicences' => 0,
        'psvDiscs' => [
            ['id' => 69],
            ['id' => 70],
        ],
        'organisation' => [
            'id' => 73,
            'name' => 'John Smith Taxis',
            'tradingNames' => [
                ['name' => 'JST Private Hire'],
            ],
            'licences' => [
                ['id' => 210],
            ],
            'leadTcArea' => ['id' => 'B'],
        ],
        'applications' => [
            ['id' => 91],
        ],
        'licenceVehicles' => [
            ['id' => 1],
            ['id' => 2],
        ],
        'operatingCentres' => [],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->mockController('\Olcs\Controller\Lva\Licence\OverviewController');
    }

    public function testCreateVariationAction()
    {
        $licenceId = 3;
        $varId = 5;

        $mockApplicationService = m::mock();
        $this->sm->setService('Entity\Application', $mockApplicationService);

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $mockApplicationService->shouldReceive('createVariation')
            ->with($licenceId)
            ->andReturn($varId);

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('lva-variation', ['application' => $varId])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->createVariationAction());
    }

    /**
     * @dataProvider indexProvider
     * @param array $overviewData
     * @param array $cases
     * @param array $expectedViewData
     */
    public function testIndexActionGet($overViewData, $cases, $expectedViewData)
    {
        $licenceId = 123;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($overViewData);

        $mockLicenceEntity
            ->shouldReceive('getShortCodeForType')
                ->with(Licence::LICENCE_TYPE_STANDARD_NATIONAL)
                ->andReturn('SN')
            ->shouldReceive('getShortCodeForType')
                ->with(Licence::LICENCE_TYPE_RESTRICTED)
                ->andReturn('R')
            ->shouldReceive('getShortCodeForType')
                ->with(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED)
                ->andReturn('SR');

        $this->mockEntity('Cases', 'getOpenForLicence')
            ->with($licenceId)
            ->andReturn($cases);

        $this->sm->setService(
            'Helper\Translation',
            m::mock()
                ->shouldReceive('translate')
                    ->with(Licence::LICENCE_STATUS_VALID)
                    ->andReturn('Valid')
                ->shouldReceive('translate')
                    ->with(Licence::LICENCE_STATUS_SURRENDERED)
                    ->andReturn('Surrendered')
                ->getMock()
        );

        $this->mockTcAreaSelect($form);

        $form->shouldReceive('setData')
            ->once()
            ->with(
                [
                    'details' => [
                        'continuationDate' => '2017-06-05',
                        'reviewDate'       => '2016-05-04',
                        'leadTcArea'       => 'B',
                    ],
                    'id' => $licenceId,
                    'version' => 1,
                ]
            )
            ->andReturnSelf();

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate');

        $this->mockRender();

        $view = $this->sut->indexAction();

        foreach ($expectedViewData as $key => $value) {
            $this->assertEquals($value, $view->getVariable($key), "'$key' not as expected");
        }
    }

    public function indexProvider()
    {
        return [
            'valid goods licence' => [
                // overviewData
                $this->overViewData1,
                // cases
                [
                    ['id' => 2], ['id' => 3], ['id' => 4]
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Haulage',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'JSH Logistics',
                    'currentApplications'        => 4,
                    'licenceNumber'              => 'OB1234567',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => 'SN',
                    'licenceStatus'              => 'Valid',
                    'surrenderedDate'            => null,
                    'numberOfVehicles'           => 5,
                    'totalVehicleAuthorisation'  => 10,
                    'numberOfOperatingCentres'   => 2,
                    'totalTrailerAuthorisation'  => 8, // goods only
                    'numberOfIssuedDiscs'        => null, // psv only
                    'numberOfCommunityLicences'  => null,
                    'openCases'                  => '3',
                    'currentReviewComplaints'    => null,
                    'originalOperatorName'       => null,
                    'originalLicenceNumber'      => null,
                    'receivesMailElectronically' => null,
                    'registeredForSelfService'   => null,
                ]
            ],
            'surrendered psv licence' => [
                // overviewData
                $this->overViewData2,
                // cases
                [
                    ['id' => 2, 'publicInquirys' => []],
                    ['id' => 3, 'publicInquirys' => []],
                    ['id' => 4, 'publicInquirys' => [ 'id' => 99]],
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Coaches',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'JSC Express',
                    'currentApplications'        => 4,
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => 'R',
                    'licenceStatus'              => 'Surrendered',
                    'surrenderedDate'            => '2015-02-11',
                    'numberOfVehicles'           => 5,
                    'totalVehicleAuthorisation'  => 10,
                    'numberOfOperatingCentres'   => 2,
                    'totalTrailerAuthorisation'  => null, // goods only
                    'numberOfIssuedDiscs'        => 6, // psv only
                    'numberOfCommunityLicences'  => 7,
                    'openCases'                  => '3 (PI)',
                    'currentReviewComplaints'    => null,
                    'originalOperatorName'       => null,
                    'originalLicenceNumber'      => null,
                    'receivesMailElectronically' => null,
                    'registeredForSelfService'   => null,
                ]
            ],
            'special restricted psv licence' => [
                // overviewData
                $this->overViewData3,
                // cases
                [
                    ['id' => 2, 'publicInquirys' => []],
                    ['id' => 3, 'publicInquirys' => []],
                    ['id' => 4, 'publicInquirys' => [ 'id' => 99]],
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Taxis',
                    'operatorId'                 => 73,
                    'numberOfLicences'           => 1,
                    'tradingName'                => 'JST Private Hire',
                    'currentApplications'        => 1,
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => 'SR',
                    'licenceStatus'              => 'Valid',
                    'surrenderedDate'            => null,
                    'numberOfVehicles'           => 2,
                    'totalVehicleAuthorisation'  => 2,
                    'numberOfOperatingCentres'   => null,
                    'totalTrailerAuthorisation'  => null,
                    'numberOfIssuedDiscs'        => 2,
                    'numberOfCommunityLicences'  => 0,
                    'openCases'                  => '3 (PI)',
                    'currentReviewComplaints'    => null,
                    'originalOperatorName'       => null,
                    'originalLicenceNumber'      => null,
                    'receivesMailElectronically' => null,
                    'registeredForSelfService'   => null,
                ]
            ],
        ];
    }

    public function testIndexActionPostValid()
    {
        $licenceId = 123;
        $organisationId = 234;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $overviewData = [
            'id' => $licenceId,
            'status' => ['id' => Licence::LICENCE_STATUS_VALID],
            'organisation' => ['id' => $organisationId],
        ];
        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($overviewData);

        $this->mockService('Helper\Translation', 'translate');

        $postData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [
                'continuationDate' => [
                    'day' => '04',
                    'month' => '03',
                    'year' => '2012'
                ],
                'reviewDate' => [
                    'day' => '11',
                    'month' => '12',
                    'year' => '2021'
                ],
                'leadTcArea' => 'B',
            ],
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate');

        $this->mockTcAreaSelect($form);

        // use a real date helper
        $dateHelper = new \Common\Service\Helper\DateHelperService();
        $this->setService('Helper\Date', $dateHelper);

        $mockLicenceEntity->shouldReceive('forceUpdate')->with(
            123,
            [
                'expiryDate' => '2012-03-04',
                'reviewDate' => '2021-12-11'

            ]
        );

        $this->mockEntity('Organisation', 'forceUpdate')
            ->with($organisationId, ['leadTcArea' => 'B']);

        $this->sut->shouldReceive('addSuccessMessage')->once();
        $this->sut->shouldReceive('reload')->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionPostInvalid()
    {
        $licenceId = 123;
        $organisationId = 234;

        $this->sut->shouldReceive('params')
            ->with('licence')
            ->andReturn($licenceId);

        $form = $this->createMockForm('LicenceOverview');

        $overviewData = $this->overViewData1;

        $mockLicenceEntity = $this->mockEntity('Licence', 'getExtendedOverview')
            ->with($licenceId)
            ->andReturn($overviewData);

        $mockLicenceEntity
            ->shouldReceive('getShortCodeForType')
                ->with(Licence::LICENCE_TYPE_STANDARD_NATIONAL)
                ->andReturn('SN');

        $this->mockService('Helper\Translation', 'translate');

        $this->mockEntity('Cases', 'getOpenForLicence')
            ->with($licenceId)
            ->andReturn([]);

        $postData = [
            'id' => $licenceId,
            'version' => '1',
            'details' => [],
        ];

        $this->setPost($postData);

        $form->shouldReceive('setData')
            ->once()
            ->with($postData)
            ->andReturnSelf();

        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(false);

        $this->getMockFormHelper()
            ->shouldReceive('remove')
            ->with($form, 'details->reviewDate');

        $this->mockTcAreaSelect($form);

        $mockLicenceEntity->shouldReceive('forceUpdate')->never();

        $this->mockEntity('Organisation', 'forceUpdate')->never();

        $this->mockRender();

        $view = $this->sut->indexAction();
    }

    protected function mockTcAreaSelect($form)
    {
        $tcAreaOptions = [
            'A' => 'Traffic area A',
            'B' => 'Traffic area A',
        ];

        $this->mockEntity('TrafficArea', 'getValueOptions')
            ->andReturn($tcAreaOptions);

        $form->shouldReceive('get')->with('details')->andReturn(
            m::mock()
                ->shouldReceive('get')
                    ->with('leadTcArea')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('setValueOptions')
                            ->with($tcAreaOptions)
                            ->getMock()
                    )
                ->getMock()
        );

    }
}
