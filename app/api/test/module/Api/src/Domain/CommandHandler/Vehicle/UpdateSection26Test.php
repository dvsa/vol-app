<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\UpdateSection26 as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Vehicle\UpdateSection26 as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;

/**
 * UpdateSection26Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateSection26Test extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Vehicle', \Dvsa\Olcs\Api\Domain\Repository\Vehicle::class);

        $this->mockedSmServices = [
            'Elasticsearch\Search' => m::mock()
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandY(): void
    {
        $data = [
            'ids' => [
                111, 222, 333,
            ],
            'section26' => 'Y'
        ];
        $command = Cmd::create($data);

        $vehicle111 = new Vehicle();
        $vehicle111->setSection26(false);
        $vehicle222 = new Vehicle();
        $vehicle222->setSection26(false);
        $vehicle333 = new Vehicle();
        $vehicle333->setSection26(false);

        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(111)->once()->andReturn($vehicle111);
        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(222)->once()->andReturn($vehicle222);
        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(333)->once()->andReturn($vehicle333);

        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle111)->once();
        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle222)->once();
        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle333)->once();

        $this->mockedSmServices['Elasticsearch\Search']->shouldReceive('updateVehicleSection26')->
            with($data['ids'], true)->once()->andReturn(true);

        $result = $this->sut->handleCommand($command);

        $this->assertTrue($vehicle111->getSection26());
        $this->assertTrue($vehicle222->getSection26());
        $this->assertTrue($vehicle333->getSection26());

        $expected = [
            'id' => [],
            'messages' => [
                'Search index updated',
                'Updated Section26 on 3 Vehicle(s).',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandN(): void
    {
        $data = [
            'ids' => [
                111, 222, 333,
            ],
            'section26' => 'F'
        ];
        $command = Cmd::create($data);

        $vehicle111 = new Vehicle();
        $vehicle111->setSection26(true);
        $vehicle222 = new Vehicle();
        $vehicle222->setSection26(true);
        $vehicle333 = new Vehicle();
        $vehicle333->setSection26(true);

        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(111)->once()->andReturn($vehicle111);
        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(222)->once()->andReturn($vehicle222);
        $this->repoMap['Vehicle']->shouldReceive('fetchById')->with(333)->once()->andReturn($vehicle333);

        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle111)->once();
        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle222)->once();
        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle333)->once();

        $this->mockedSmServices['Elasticsearch\Search']->shouldReceive('updateVehicleSection26')->
            with($data['ids'], false)->once()->andReturn(false);

        $result = $this->sut->handleCommand($command);

        $this->assertFalse($vehicle111->getSection26());
        $this->assertFalse($vehicle222->getSection26());
        $this->assertFalse($vehicle333->getSection26());

        $expected = [
            'id' => [],
            'messages' => [
                'Search index update error',
                'Updated Section26 on 3 Vehicle(s).',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
