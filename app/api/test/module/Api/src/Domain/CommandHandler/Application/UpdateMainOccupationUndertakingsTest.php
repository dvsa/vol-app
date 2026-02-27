<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateMainOccupationUndertakings as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateMainOccupationUndertakings as Command;
use Mockery as m;

class UpdateMainOccupationUndertakingsTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'main occupation undertakings updated';
    protected array $commandData = [
        'psvOccupationRecordsConfirmation' => 'Y',
        'psvIncomeRecordsConfirmation' => 'Y',
    ];
    protected array $sections = [
        'psvMainOccupationUndertakings',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('updateMainOccupationUndertakings')
            ->with(
                $this->commandData['psvOccupationRecordsConfirmation'],
                $this->commandData['psvIncomeRecordsConfirmation']
            )
            ->andReturnSelf();

        return $application;
    }
}
