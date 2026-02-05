<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateWrittenExplanation as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateWrittenExplanation as Command;
use Mockery as m;

class UpdateWrittenExplanationTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'vehicle small part written updated';
    protected array $commandData = [
        'psvSmallVhlNotes' => 'Some notes',
        'psvTotalVehicleSmall' => 2,
        'psvTotalVehicleLarge' => 3,
    ];
    protected array $sections = [
        'psvSmallPartWritten',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('updateWrittenEvidence')
            ->with(
                $this->commandData['psvSmallVhlNotes'],
                $this->commandData['psvTotalVehicleSmall'],
                $this->commandData['psvTotalVehicleLarge']
            )
            ->andReturnSelf();

        return $application;
    }
}
