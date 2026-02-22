<?php

declare(strict_types=1);

/**
 * Update Ta Authority Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateTaAuthority;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateTaAuthority as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Update TaAuthorityTest
 */
class UpdateTaAuthorityTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateTaAuthority();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            LocalAuthorityEntity::class => [
                5 => m::mock(LocalAuthorityEntity::class)
            ],
            TrafficAreaEntity::class => [
                'M' => m::mock(TrafficAreaEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $stoppingArrangements = 'stoppingArrangements';

        $command = Cmd::Create(
            [
                'id' => 99,
                'stoppingArrangements' => $stoppingArrangements,
                'localAuthoritys' => [0 => 5],
                'trafficAreas' => [0 => 'M']
            ]
        );

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->shouldReceive('updateTaAuthority')
        ->once()
        ->with($stoppingArrangements)
        ->shouldReceive('setLocalAuthoritys')
        ->with(m::type(ArrayCollection::class))
        ->once()
        ->shouldReceive('setTrafficAreas')
        ->with(m::type(ArrayCollection::class))
        ->once();

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusRegEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
    }
}
