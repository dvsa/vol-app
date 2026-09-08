<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\OperatingCentresReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * OperatingCentres review service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class OperatingCentresReviewServiceTest extends MockeryTestCase
{
    /** @var OperatingCentresReviewService review service */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new OperatingCentresReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetConfigFromData')]
    public function testGetConfigFromData(
        mixed $canHaveTrailer,
        mixed $isVehicleTypeMixedWithLgv,
        array $licenceOperatingCentreSpecs,
        mixed $expected
    ): void {
        $continuationDetail = new ContinuationDetail();

        $licenceOperatingCentres = new ArrayCollection(
            array_map(fn (array $spec) => $this->createLicenceOperatingCentre(...$spec), $licenceOperatingCentreSpecs)
        );

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getOperatingCentres')
            ->andReturn($licenceOperatingCentres)
            ->once()
            ->shouldReceive('canHaveTrailer')
            ->andReturn($canHaveTrailer)
            ->withNoArgs()
            ->shouldReceive('isVehicleTypeMixedWithLgv')
            ->andReturn($isVehicleTypeMixedWithLgv)
            ->withNoArgs()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    public static function dpGetConfigFromData(): \Iterator
    {
        yield 'licence cannot have trailers, vehicle type not mixed with lgv' => [
            false,
            false,
            [['Foo', 'Bar', 1, 2], ['Baz', 'Cake', 3, 4]],
            [
                [
                    ['value' => 'continuations.oc-section.table.name', 'header' => true],
                    ['value' => 'continuations.oc-section.table.vehicles', 'header' => true]
                ],
                [
                    ['value' => 'Baz, Cake'],
                    ['value' => '3']
                ],
                [
                    ['value' => 'Foo, Bar'],
                    ['value' => '1']
                ]
            ]
        ];
        yield 'licence cannot have trailers, vehicle type mixed with lgv' => [
            false,
            true,
            [['Foo', 'Bar', 1, 2], ['Baz', 'Cake', 3, 4]],
            [
                [
                    ['value' => 'continuations.oc-section.table.name', 'header' => true],
                    ['value' => 'continuations.oc-section.table.heavy-goods-vehicles', 'header' => true]
                ],
                [
                    ['value' => 'Baz, Cake'],
                    ['value' => '3']
                ],
                [
                    ['value' => 'Foo, Bar'],
                    ['value' => '1']
                ]
            ]
        ];
        yield 'licence can have trailers, vehicle type not mixed with lgv' => [
            true,
            false,
            [['Foo', 'Bar', 1, 2], ['Baz', 'Cake', 3, 4]],
            [
                [
                    ['value' => 'continuations.oc-section.table.name', 'header' => true],
                    ['value' => 'continuations.oc-section.table.vehicles', 'header' => true],
                    ['value' => 'continuations.oc-section.table.trailers', 'header' => true]
                ],
                [
                    ['value' => 'Baz, Cake'],
                    ['value' => '3'],
                    ['value' => '4']
                ],
                [
                    ['value' => 'Foo, Bar'],
                    ['value' => '1'],
                    ['value' => '2']
                ]
            ]
        ];
        yield 'licence can have trailers, vehicle type mixed with lgv' => [
            true,
            true,
            [['Foo', 'Bar', 1, 2], ['Baz', 'Cake', 3, 4]],
            [
                [
                    ['value' => 'continuations.oc-section.table.name', 'header' => true],
                    ['value' => 'continuations.oc-section.table.heavy-goods-vehicles', 'header' => true],
                    ['value' => 'continuations.oc-section.table.trailers', 'header' => true]
                ],
                [
                    ['value' => 'Baz, Cake'],
                    ['value' => '3'],
                    ['value' => '4']
                ],
                [
                    ['value' => 'Foo, Bar'],
                    ['value' => '1'],
                    ['value' => '2']
                ]
            ]
        ];
        yield 'no operating centres' => [
            false,
            false,
            [],
            [],
        ];
    }

    private function createLicenceOperatingCentre(string $addressLine1, string $town, int $vehicles, int $trailers): m\MockInterface
    {
        return m::mock()
            ->shouldReceive('getOperatingCentre')
            ->withNoArgs()
            ->andReturn(
                m::mock()
                    ->shouldReceive('getAddress')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getAddressLine1')
                            ->andReturn($addressLine1)
                            ->withNoArgs()
                            ->shouldReceive('getTown')
                            ->andReturn($town)
                            ->withNoArgs()
                            ->getMock()
                    )
                    ->withNoArgs()
                    ->getMock()
            )
            ->shouldReceive('getNoOfVehiclesRequired')
            ->andReturn($vehicles)
            ->withNoArgs()
            ->shouldReceive('getNoOfTrailersRequired')
            ->andReturn($trailers)
            ->withNoArgs()
            ->getMock();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetSummaryFromData')]
    public function testGetSummaryFromData(mixed $applicableAuthProperties, mixed $expected): void
    {
        $continuationDetail = new ContinuationDetail();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getTotAuthVehicles')
            ->withNoArgs()
            ->andReturn(8)
            ->shouldReceive('getTotAuthHgvVehicles')
            ->withNoArgs()
            ->andReturn(5)
            ->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn(3)
            ->shouldReceive('getTotAuthTrailers')
            ->withNoArgs()
            ->andReturn(2)
            ->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->andReturn($applicableAuthProperties)
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $this->assertEquals($expected, $this->sut->getSummaryFromData($continuationDetail));
    }

    public static function dpGetSummaryFromData(): \Iterator
    {
        yield 'vehicles and trailers' => [
            [
                'totAuthVehicles',
                'totAuthTrailers',
            ],
            [
                [
                    ['value' => 'continuations.oc-section.table.vehicles', 'header' => true],
                    ['value' => 8]
                ],
                [
                    ['value' => 'continuations.oc-section.table.trailers', 'header' => true],
                    ['value' => 2]
                ]
            ]
        ];
        yield 'hgv, lgv and trailers' => [
            [
                'totAuthHgvVehicles',
                'totAuthLgvVehicles',
                'totAuthTrailers',
            ],
            [
                [
                    ['value' => 'continuations.oc-section.table.heavy-goods-vehicles', 'header' => true],
                    ['value' => 5]
                ],
                [
                    ['value' => 'continuations.oc-section.table.light-goods-vehicles', 'header' => true],
                    ['value' => 3]
                ],
                [
                    ['value' => 'continuations.oc-section.table.trailers', 'header' => true],
                    ['value' => 2]
                ]
            ]
        ];
    }

    public function testGetSummaryHeader(): void
    {
        $this->assertEquals(
            'continuations.oc-section.table.authorisation',
            $this->sut->getSummaryHeader()
        );
    }
}
