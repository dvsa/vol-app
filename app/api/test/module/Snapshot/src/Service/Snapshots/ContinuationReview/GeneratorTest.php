<?php

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Generator;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Lva\SectionAccessService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\OperatingCentresReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;

/**
 * Generator Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GeneratorTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|AbstractGeneratorServices
     */
    protected $abstractGeneratorServices;

    /**
     * @var \Mockery\MockInterface|SectionAccessService
     */
    protected $sectionAccessService;

    /**
     * @var \Mockery\MockInterface|NiTextTranslation
     */
    protected $niTextTranslation;

    /**
     * @var Generator
     */
    protected $sut;

    protected $services;

    protected PhpRenderer $viewRenderer;

    public function setUp(): void
    {
        $sm = m::mock(ServiceLocatorInterface::class);

        $this->services = [
            'ContinuationReview\TypeOfLicence' => m::mock(),
            'ContinuationReview\OperatingCentres' => m::mock(OperatingCentresReviewService::class)->makePartial(),
            'ViewRenderer' => m::mock()
        ];

        $sm->shouldReceive('get')->andReturnUsing(
            fn($key) => $this->services[$key]
        );
        $sm->shouldReceive('has')->andReturnUsing(
            fn($key) => array_key_exists($key, $this->services)
        );

        $this->viewRenderer = m::mock(PhpRenderer::class);

        $abstractGeneratorServices = m::mock(AbstractGeneratorServices::class);
        $abstractGeneratorServices->shouldReceive('getRenderer')
            ->withNoArgs()
            ->andReturn($this->viewRenderer);

        $this->sectionAccessService = m::mock(SectionAccessService::class);

        $this->niTextTranslation = m::mock(NiTextTranslation::class);

        $this->sut = new Generator(
            $abstractGeneratorServices,
            $this->sectionAccessService,
            $this->niTextTranslation,
            $sm
        );
    }

    /**
     * @dataProvider licenceTypeProvider
     */
    public function testGenerate($isPsv, $licenceType, $vehicleType, $expectedSections)
    {
        $mockLicence = $this->setUpLicence($isPsv, $licenceType, $vehicleType);
        $mockContinuationDetail = $this->setUpContinuationDetail($mockLicence);
        $this->setUpServices($mockLicence, $mockContinuationDetail, $this->getSections());

        /** @var ViewModel $result */
        $result = $this->sut->generate($mockContinuationDetail);

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('layout/continuation-review', $result->getTemplate());

        $params = $result->getVariables();

        $expected = [
            'reviewTitle' => 'BAR LTD OB123',
            'subTitle' => 'continuation-review-subtitle',
            'sections' => $expectedSections
        ];

        $this->assertEquals($expected, $params);
    }

    protected function getSections()
    {
        return [
            'type_of_licence' => 'foo' ,
            'operating_centres' => 'foo' ,
            Generator::PEOPLE_SECTION => 'foo',
            Generator::TRAILERS_SECTION => 'foo',
            Generator::TAXI_PHV_SECTION => 'foo',
            Generator::DISCS_SECTION => 'foo',
            Generator::COMMUNITY_LICENCES_SECTION => 'foo',
            Generator::CONDITIONS_UNDERTAKINGS_SECTION => 'foo'
        ];
    }

    protected function setUpLicence(bool $isPsv, string $licenceType, string $vehicleType)
    {
        $isRestricted = $licenceType == Licence::LICENCE_TYPE_RESTRICTED;

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceType)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getVehicleType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($vehicleType)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('isRestricted')
            ->andReturn($isRestricted)
            ->once()
            ->shouldReceive('getConditionUndertakings')
            ->andReturn([])
            ->once()
            ->shouldReceive('getOrganisation')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn('org_typ_rc')
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->shouldReceive('getName')
                    ->andReturn('BAR LTD')
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('getLicNo')
            ->andReturn('OB123')
            ->once();

        if ($isRestricted) {
            $mockLicence->shouldReceive('isPsv')
                ->andReturn($isPsv)
                ->once();
        }

        return $mockLicence->getMock();
    }

    protected function setUpContinuationDetail($mockLicence)
    {
        return m::mock(ContinuationDetail::class)
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->times(5)
            ->getMock();
    }

    protected function setUpServices($mockLicence, $mockContinuationDetail, $sections)
    {
        $this->niTextTranslation
            ->shouldReceive('setLocaleForNiFlag')
            ->with('N')
            ->once();

        $this->sectionAccessService
            ->shouldReceive('getAccessibleSectionsForLicenceContinuation')
            ->with($mockLicence)
            ->andReturn($sections)
            ->once();

        $this->services['ContinuationReview\TypeOfLicence']
            ->shouldReceive('getConfigFromData')
            ->with($mockContinuationDetail)
            ->once()
            ->andReturn('type-of-licence');

        $this->services['ContinuationReview\OperatingCentres']
            ->shouldReceive('getConfigFromData')
            ->with($mockContinuationDetail)
            ->andReturn('operating-centres')
            ->once()
            ->shouldReceive('getSummaryFromData')
            ->with($mockContinuationDetail)
            ->andReturn('operating-centres-summary')
            ->once()
            ->shouldReceive('getSummaryHeader')
            ->with($mockContinuationDetail)
            ->andReturn('operating-centres-summary-header')
            ->once();

        $this->viewRenderer->shouldReceive('render')
            ->once()
            ->with(m::type(ViewModel::class))
            ->andReturnUsing(
                fn($view) => $view
            );
    }

    public function licenceTypeProvider()
    {
        return [
            'NotPsvAndNotRestricted' => [
                'isPsv' => false,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectedSections' => [
                    [
                        'header' => 'continuation-review-type_of_licence',
                        'config' => 'type-of-licence'
                    ],
                    [
                        'header' => 'continuation-review-operating_centres',
                        'config' => 'operating-centres',
                        'summary' => 'operating-centres-summary',
                        'summaryHeader' => 'operating-centres-summary-header',
                    ],
                    [
                        'header' => 'continuation-review-people-org_typ_rc',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-finance',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-declaration',
                        'config' => ''
                    ],
                ]
            ],
            'IsPsvAndNotRestricted' => [
                'isPsv' => true,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectedSections' => [
                    [
                        'header' => 'continuation-review-type_of_licence',
                        'config' => 'type-of-licence'
                    ],
                    [
                        'header' => 'continuation-review-operating_centres',
                        'config' => 'operating-centres',
                        'summary' => 'operating-centres-summary',
                        'summaryHeader' => 'operating-centres-summary-header',
                    ],
                    [
                        'header' => 'continuation-review-people-org_typ_rc',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-finance',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-declaration',
                        'config' => ''
                    ],
                ]
            ],
            'NotPsvAndIsRestricted' => [
                'isPsv' => false,
                'licenceType' => Licence::LICENCE_TYPE_RESTRICTED,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'expectedSections' => [
                    [
                        'header' => 'continuation-review-type_of_licence',
                        'config' => 'type-of-licence'
                    ],
                    [
                        'header' => 'continuation-review-operating_centres',
                        'config' => 'operating-centres',
                        'summary' => 'operating-centres-summary',
                        'summaryHeader' => 'operating-centres-summary-header',
                    ],
                    [
                        'header' => 'continuation-review-people-org_typ_rc',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-finance',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-declaration',
                        'config' => ''
                    ],
                ]
            ],
            'IsPsvAndIsRestricted' => [
                'isPsv' => true,
                'licenceType' => Licence::LICENCE_TYPE_RESTRICTED,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'expectedSections' => [
                    [
                        'header' => 'continuation-review-type_of_licence',
                        'config' => 'type-of-licence'
                    ],
                    [
                        'header' => 'continuation-review-operating_centres',
                        'config' => 'operating-centres',
                        'summary' => 'operating-centres-summary',
                        'summaryHeader' => 'operating-centres-summary-header',
                    ],
                    [
                        'header' => 'continuation-review-people-org_typ_rc',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-finance',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-conditions_undertakings',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-declaration',
                        'config' => ''
                    ],
                ]
            ],
            'IsStandardInternationalLgv' => [
                'isPsv' => true,
                'licenceType' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'expectedSections' => [
                    [
                        'header' => 'continuation-review-type_of_licence',
                        'config' => 'type-of-licence'
                    ],
                    [
                        'header' => 'continuation-review-operating_centres.lgv',
                        'config' => 'operating-centres',
                        'summary' => 'operating-centres-summary',
                        'summaryHeader' => 'operating-centres-summary-header',
                    ],
                    [
                        'header' => 'continuation-review-people-org_typ_rc',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-finance',
                        'config' => ''
                    ],
                    [
                        'header' => 'continuation-review-declaration',
                        'config' => ''
                    ],
                ]
            ]
        ];
    }
}
