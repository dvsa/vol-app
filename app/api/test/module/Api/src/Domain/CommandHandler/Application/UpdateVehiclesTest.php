<?php

declare(strict_types=1);

/**
 * Update Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateVehicles;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicles as Cmd;

/**
 * Update Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateVehiclesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateVehicles();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutPartialWithoutVehicles(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'id' => 111,
            'version' => 1,
            'hasEnteredReg' => 'Y',
            'partial' => null
        ];
        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application->shouldReceive('getActiveVehicles->count')
            ->andReturn(0);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithVehicles(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'hasEnteredReg' => 'Y',
            'partial' => null
        ];
        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application->shouldReceive('getActiveVehicles->count')
            ->andReturn(1);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'vehicles'
        ];
        $result1 = new Result();
        $result1->addMessage('Section Updated');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => [],
            'messages' => [
                'Application updated',
                'Section Updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('Y', $application->getHasEnteredReg());
    }

    public function testHandleCommandWithoutVehiclesWithPartial(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'hasEnteredReg' => 'Y',
            'partial' => true
        ];
        $command = Cmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();

        $application->shouldReceive('getActiveVehicles->count')
            ->andReturn(0);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $data = [
            'id' => 111,
            'section' => 'vehicles'
        ];
        $result1 = new Result();
        $result1->addMessage('Section Updated');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => [],
            'messages' => [
                'Application updated',
                'Section Updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('Y', $application->getHasEnteredReg());
    }
}
