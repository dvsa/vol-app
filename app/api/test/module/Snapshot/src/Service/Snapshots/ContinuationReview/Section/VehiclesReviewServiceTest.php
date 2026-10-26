<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\Application;
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

        $licenceVehicles = new ArrayCollection();

        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM456')
                    ->once()
                    ->shouldReceive('getPlatedWeight')
                    ->andReturn(1000)
                    ->times($isGoods ? 1 : 0)
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getremovalDate')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getSpecifiedDate')
            ->andReturn('2010-01-01')
            ->getMock();
        $licenceVehicle1->setApplication(m::mock(Application::class));

        $licenceVehicle2 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM123')
                    ->once()
                    ->shouldReceive('getPlatedWeight')
                    ->andReturn(2000)
                    ->times($isGoods ? 1 : 0)
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getremovalDate')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getSpecifiedDate')
            ->andReturn('2010-01-01')
            ->getMock();

        $licenceVehicle3 = m::mock()
            ->shouldReceive('getremovalDate')
            ->andReturn('2010-01-01')
            ->once()
            ->shouldReceive('getSpecifiedDate')
            ->andReturn('2010-01-01')
            ->getMock();

        $unspecifiedVehicle = m::mock()
            ->shouldReceive('getRemovalDate')->andReturn(null)
            ->shouldReceive('getSpecifiedDate')->andReturn(null)
            ->shouldReceive('getVehicle')->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')->andReturn('UNSPECIFIED')
                    ->shouldReceive('getPlatedWeight')->andReturn(3000)
                    ->getMock()
            )
            ->getMock();

        $licenceVehicles->add($licenceVehicle1);
        $licenceVehicles->add($licenceVehicle2);
        $licenceVehicles->add($licenceVehicle3);
        $licenceVehicles->add($unspecifiedVehicle);

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getLicenceVehicles')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('getGoodsOrPsv')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn($category)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

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
