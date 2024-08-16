<?php

/**
 * DeleteApplicationOperatingCentreTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationOperatingCentre;

use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationOperatingCentre\DeleteApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class DeleteApplicationOperatingCentreTest
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\ConditionUndertaking
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class DeleteApplicationOperatingCentreTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteApplicationOperatingCentre();
        $this->mockRepo('ApplicationOperatingCentre', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->references = [
            S4::class => [
                1 => m::mock(S4::class)->makePartial()
            ],
            ApplicationOperatingCentre::class => [
                m::mock(ApplicationOperatingCentre::class)->makePartial()
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $command = Cmd::create(
            [
                's4' => 1
            ]
        );

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('fetchByS4')
            ->andReturn(
                [
                    $this->references[ApplicationOperatingCentre::class]
                ]
            );

        $this->repoMap['ApplicationOperatingCentre']
            ->shouldReceive('delete')
            ->once();

        $this->sut->handleCommand($command);
    }
}
