<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\VehiclesReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Laminas\I18n\Translator\TranslatorInterface;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Vehicles review service test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class VehiclesReviewServiceTest extends MockeryTestCase
{
    /** @var VehiclesReviewService review service */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);
        $mockTranslator->shouldReceive('translate')
            ->with('There are no vehicles recorded on your licence')
            ->andReturn('Translated empty vehicle message');

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new VehiclesReviewService($abstractReviewServiceServices);
    }

    #[TestWith([Licence::LICENCE_CATEGORY_GOODS_VEHICLE])]
    #[TestWith([Licence::LICENCE_CATEGORY_PSV])]
    public function testGetConfigFromData(string $category): void
    {
        $isGoods = $category === Licence::LICENCE_CATEGORY_GOODS_VEHICLE;
        $continuationDetail = new ContinuationDetail();

        $mockLicence = m::mock(Licence::class)->makePartial();
        $mockLicence->setGoodsOrPsv(new RefData($category));

        $licenceVehicle1 = $this->licenceVehicle($mockLicence, 'VRM456', 1000, '2010-01-01T00:00:00+0000', null);
        $licenceVehicle2 = $this->licenceVehicle($mockLicence, 'VRM123', 2000, '2010-01-01T00:00:00+0000', null);
        $removedVehicle = $this->licenceVehicle(
            $mockLicence,
            'REMOVED',
            4000,
            '2010-01-01T00:00:00+0000',
            '2011-01-01T00:00:00+0000'
        );
        $unspecifiedVehicle = $this->licenceVehicle($mockLicence, 'UNSPECIFIED', 3000, null, null);

        $mockLicence->setLicenceVehicles(new ArrayCollection([
            $licenceVehicle1,
            $licenceVehicle2,
            $removedVehicle,
            $unspecifiedVehicle,
        ]));

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuations.vehicles-section.table.vrm', 'header' => true],
                ['value' => 'continuations.vehicles-section.table.weight', 'header' => true]
            ],
            [
                ['value' => 'VRM123'],
                ['value' => '2000kg']
            ],
            [
                ['value' => 'VRM456'],
                ['value' => '1000kg']
            ]
        ];

        if (!$isGoods) {
            foreach ($expected as &$row) {
                unset($row[1]);
            }
            unset($row);
        }

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    private function licenceVehicle(
        Licence $licence,
        string $vrm,
        int $platedWeight,
        ?string $specifiedDate,
        ?string $removalDate
    ): LicenceVehicle {
        $vehicle = new Vehicle();
        $vehicle->setVrm($vrm);
        $vehicle->setPlatedWeight($platedWeight);

        $licenceVehicle = new LicenceVehicle($licence, $vehicle);
        $licenceVehicle->setSpecifiedDate($specifiedDate);
        $licenceVehicle->setRemovalDate($removalDate);

        return $licenceVehicle;
    }

    public function testGetConfigFromDataWithOnlyUnspecifiedVehicles(): void
    {
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setGoodsOrPsv(new RefData(Licence::LICENCE_CATEGORY_GOODS_VEHICLE));
        $licence->setLicenceVehicles(new ArrayCollection([
            new LicenceVehicle($licence, new Vehicle()),
        ]));
        $continuationDetail = new ContinuationDetail();
        $continuationDetail->setLicence($licence);

        $this->assertEquals(
            ['emptyTableMessage' => 'Translated empty vehicle message'],
            $this->sut->getConfigFromData($continuationDetail)
        );
    }
}
