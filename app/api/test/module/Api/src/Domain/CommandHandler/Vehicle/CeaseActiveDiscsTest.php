<?php

declare(strict_types=1);

/**
 * Cease Active Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\CeaseActiveDiscs;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CeaseActiveDiscs as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

/**
 * Cease Active Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CeaseActiveDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CeaseActiveDiscs();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111, 222, 333
            ]
        ];
        $command = Cmd::create($data);

        /** @var LicenceVehicleEntity $licenceVehicle1 */
        $goodsDiscs1 = new ArrayCollection();
        $licenceVehicle1 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle1->setGoodsDiscs($goodsDiscs1);

        /** @var LicenceVehicleEntity $licenceVehicle2 */
        $goodsDisc2 = m::mock(GoodsDisc::class)->makePartial();
        $goodsDiscs2 = new ArrayCollection();
        $goodsDiscs2->add($goodsDisc2);
        $licenceVehicle2 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle2->setGoodsDiscs($goodsDiscs2);

        /** @var LicenceVehicleEntity $licenceVehicle3 */
        $goodsDisc3 = m::mock(GoodsDisc::class)->makePartial();
        $goodsDisc3->setCeasedDate(new \DateTime('2010-01-01'));
        $goodsDiscs3 = new ArrayCollection();
        $goodsDiscs3->add($goodsDisc3);
        $licenceVehicle3 = m::mock(LicenceVehicleEntity::class)->makePartial();
        $licenceVehicle3->setGoodsDiscs($goodsDiscs3);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licenceVehicle1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($licenceVehicle2)
            ->shouldReceive('fetchById')
            ->with(333)
            ->andReturn($licenceVehicle3);

        $this->repoMap['GoodsDisc']->shouldReceive('save')
            ->once()
            ->with($goodsDisc2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Disc(s) Ceased'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
