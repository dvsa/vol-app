<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\DeleteConditionUndertakingS4;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\CancelS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Schedule41Cancel;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Cancel as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Class Schedule41CancelTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Schedule41CancelTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Schedule41Cancel();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            S4::STATUS_REFUSED,
            S4::STATUS_CANCELLED,
            S4::STATUS_APPROVED,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
        ];

        $command = Cmd::create($data);

        $application = $this->getTestingApplication();

        $this->repoMap['Application']->shouldReceive('fetchById')->once()->andReturn($application);

        $s4 = new S4($application, $application->getLicence());
        $s4->setId(2309);
        $application->addS4s($s4);

        $s4Cancelled = new S4($application, $application->getLicence());
        $s4Cancelled->setId(2601);
        $s4Cancelled->setOutcome($this->refData[S4::STATUS_CANCELLED]);
        $application->addS4s($s4Cancelled);

        $s4Approved = new S4($application, $application->getLicence());
        $s4Approved->setId(2602);
        $s4Approved->setOutcome($this->refData[S4::STATUS_APPROVED]);
        $application->addS4s($s4Approved);

        $s4Refused = new S4($application, $application->getLicence());
        $s4Refused->setId(2603);
        $s4Refused->setOutcome($this->refData[S4::STATUS_REFUSED]);
        $application->addS4s($s4Refused);

        $this->expectedSideEffect(
            CancelS4::class,
            ['id' => 2309],
            new Result()
        );
        $this->expectedSideEffect(
            DeleteApplicationOperatingCentre::class,
            ['s4' => 2309],
            new Result()
        );
        $this->expectedSideEffect(
            DeleteConditionUndertakingS4::class,
            ['s4' => 2309],
            new Result()
        );

        $this->expectedSideEffect(
            CancelS4::class,
            ['id' => 2602],
            new Result()
        );
        $this->expectedSideEffect(
            DeleteApplicationOperatingCentre::class,
            ['s4' => 2602],
            new Result()
        );
        $this->expectedSideEffect(
            DeleteConditionUndertakingS4::class,
            ['s4' => 2602],
            new Result()
        );

        $this->sut->handleCommand($command);
    }
}
