<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\SubmitApplication;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * PublishTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Domain\CommandHandler\Application\Publish();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        $this->mockedSmServices = [
            'ApplicationPublishValidationService' => m::mock(),
            'VariationPublishValidationService' => m::mock(),
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = m::mock(\Dvsa\Olcs\Transfer\Command\CommandInterface::class);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(2409);
        $application->setIsVariation(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->andReturn($application);

        $this->mockedSmServices['ApplicationPublishValidationService']->shouldReceive('validate')->with($application)
            ->andReturn([]);
        $application->shouldReceive('getTrafficArea->getId')->with()->once()->andReturn('TA');

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            [
                'id' => 2409,
                'trafficArea' => 'TA',
            ],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::class,
            [
                'id' => 2409,
            ],
            new Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandValidationErrors(): void
    {
        $command = m::mock(\Dvsa\Olcs\Transfer\Command\CommandInterface::class);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(2409);
        $application->setIsVariation(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->andReturn($application);

        $this->mockedSmServices['ApplicationPublishValidationService']->shouldReceive('validate')->with($application)
            ->andReturn(['ERROR']);

        try {
            $this->sut->handleCommand($command);
            $this->fail('Exception should have been thrown');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertSame($e->getMessages(), ['ERROR']);
        }
    }
}
