<?php

declare(strict_types=1);

/**
 * Update Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdatePsvVehicles as CommandHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePsvVehicles as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Psv Vehicles Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdatePsvVehiclesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutVehicles(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'partial' => false,
                'hasEnteredReg' => 'Y'
            ]
        );

        /** @var Entity\Application\Application $application */
        $application = m::mock(Entity\Application\Application::class)->makePartial();
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application);

        $licenceVehicles = new ArrayCollection();

        $this->repoMap['LicenceVehicle']->shouldReceive('getAllPsvVehicles')
            ->with($application)
            ->andReturn($licenceVehicles);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithVehicles(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'partial' => false,
                'hasEnteredReg' => 'Y'
            ]
        );

        /** @var Entity\Application\Application $application */
        $application = m::mock(Entity\Application\Application::class)->makePartial();
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($application)
            ->shouldReceive('save')
            ->with($application);

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle1);

        $this->repoMap['LicenceVehicle']->shouldReceive('getAllPsvVehicles')
            ->with($application)
            ->andReturn($licenceVehicles);

        $data = [
            'id' => 111,
            'section' => 'vehiclesPsv'
        ];
        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Application updated',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('Y', $application->getHasEnteredReg());
    }
}
