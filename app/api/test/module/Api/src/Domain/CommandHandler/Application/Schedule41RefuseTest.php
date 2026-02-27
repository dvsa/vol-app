<?php

declare(strict_types=1);

/**
 * Schedule41RefuseTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\DeleteConditionUndertakingS4;
use Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\DisassociateS4;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\RefuseS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Schedule41Refuse;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Command\Application\Schedule41Refuse as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class Schedule41RefuseTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41RefuseTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Schedule41Refuse();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
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

        $loc = new \Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre(
            $application->getLicence(),
            new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre()
        );
        $s4->setLicence($this->getTestingLicence());
        $s4->getLicence()->addOperatingCentres($loc);

        $this->expectedSideEffect(
            RefuseS4::class,
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

        $this->sut->handleCommand($command);
    }
}
