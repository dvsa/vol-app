<?php

declare(strict_types=1);

/**
 * Create Goods Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\CreateGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsDiscs as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as LicenceVehicleEntity;

/**
 * Create Goods Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateGoodsDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateGoodsDiscs();
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            LicenceVehicleEntity::class => [
                111 => m::mock(LicenceVehicleEntity::class),
                222 => m::mock(LicenceVehicleEntity::class),
                333 => m::mock(LicenceVehicleEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111, 222, 333
            ],
            'isCopy' => 'N'
        ];
        $command = Cmd::create($data);

        $saved = [];

        $this->repoMap['GoodsDisc']->shouldReceive('save')
            ->with(m::type(GoodsDisc::class))
            ->andReturnUsing(
                function (GoodsDisc $gd) use (&$saved) {
                    $saved[] = $gd;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '3 Disc(s) created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertCount(3, $saved);

        $this->assertEquals('N', $saved[0]->getIsCopy());
        $this->assertEquals('N', $saved[1]->getIsCopy());
        $this->assertEquals('N', $saved[2]->getIsCopy());

        $this->assertSame($this->references[LicenceVehicleEntity::class][111], $saved[0]->getLicenceVehicle());
        $this->assertSame($this->references[LicenceVehicleEntity::class][222], $saved[1]->getLicenceVehicle());
        $this->assertSame($this->references[LicenceVehicleEntity::class][333], $saved[2]->getLicenceVehicle());
    }
}
