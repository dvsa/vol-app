<?php

declare(strict_types=1);

/**
 * Update Stops Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateStops;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateStops as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Stops Test
 */
final class UpdateStopsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateStops();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var BusEntity $busReg */
        $busReg = m::mock(BusEntity::class)->makePartial();
        $busReg->initCollections();
        $busReg->shouldReceive('updateStops')
            ->once()
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $command->getVersion())
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('subsidyAreaProvider')]
    public function testSavesSubsidyProvidersIndependentlyOfRouteCoverage(bool $matchingArea): void
    {
        $busReg = m::mock(BusEntity::class)->makePartial();
        $busReg->initCollections();
        $busReg->shouldReceive('canEdit')->andReturn(true);
        $routeAreas = $busReg->getTrafficAreas();
        $routeAuthorities = $busReg->getLocalAuthoritys();
        $area = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $area->setId('F');
        $authority = new \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority();
        $authority->setId(87);
        $authority->setTrafficArea($matchingArea ? $area : (new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea())->setId('B'));
        $command = Cmd::create([
            'id' => 99,
            'subsidyTrafficAreas' => ['F'],
            'subsidyLocalAuthorities' => [87],
            'subsidyDetail' => 'Existing comments',
        ]);
        $this->repoMap['Bus']->shouldReceive('fetchUsingId')->andReturn($busReg);
        $this->references[\Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea::class]['F'] = $area;
        $this->references[\Dvsa\Olcs\Api\Entity\Bus\LocalAuthority::class][87] = $authority;
        $this->repoMap['Bus']->shouldReceive('save')->times($matchingArea ? 1 : 0)->with($busReg);
        if (!$matchingArea) {
            $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        }

        $this->sut->handleCommand($command);

        $this->assertSame([$area], $busReg->getSubsidyTrafficAreas()->toArray());
        $this->assertSame([$authority], $busReg->getSubsidyLocalAuthorities()->toArray());
        $this->assertSame($routeAreas, $busReg->getTrafficAreas());
        $this->assertSame($routeAuthorities, $busReg->getLocalAuthoritys());
        $this->assertSame('Existing comments', $busReg->getSubsidyDetail());
    }
    public static function subsidyAreaProvider(): array
    {
        return [[true], [false]];
    }
    public function testClearsProvidersAndKeepsLegacyValues(): void
    {
        $busReg = m::mock(BusEntity::class)->makePartial();
        $busReg->initCollections();
        $busReg->shouldReceive('canEdit')->andReturn(true);
        $busReg->setSubsidyTrafficAreas(new \Doctrine\Common\Collections\ArrayCollection([
            new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea(),
        ]));
        $busReg->setSubsidyLocalAuthorities(new \Doctrine\Common\Collections\ArrayCollection([
            new \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority(),
        ]));
        $subsidised = new \Dvsa\Olcs\Api\Entity\System\RefData('bs_in_part');
        $this->refData['bs_in_part'] = $subsidised;
        $this->repoMap['Bus']->shouldReceive('fetchUsingId')->andReturn($busReg);
        $this->repoMap['Bus']->shouldReceive('save')->once()->with($busReg);
        $this->sut->handleCommand(Cmd::create([
            'id' => 99,
            'subsidyTrafficAreas' => [],
            'subsidyLocalAuthorities' => [],
            'subsidised' => 'bs_in_part',
            'subsidyDetail' => 'Historical comments',
        ]));
        $this->assertCount(0, $busReg->getSubsidyTrafficAreas());
        $this->assertCount(0, $busReg->getSubsidyLocalAuthorities());
        $this->assertSame($subsidised, $busReg->getSubsidised());
        $this->assertSame('Historical comments', $busReg->getSubsidyDetail());
    }
}
