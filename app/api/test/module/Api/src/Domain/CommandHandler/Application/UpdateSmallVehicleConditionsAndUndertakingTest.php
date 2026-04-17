<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateSmallVehicleConditionsAndUndertaking as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateSmallVehicleConditionsAndUndertaking as Command;
use Mockery as m;

class UpdateSmallVehicleConditionsAndUndertakingTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'small vehicle conditions updated';
    protected array $commandData = [
        'psvSmallVhlConfirmation' => 'Y',
    ];
    protected array $sections = [
        'psvSmallConditions',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('setPsvSmallVhlConfirmation')
            ->with($this->commandData['psvSmallVhlConfirmation'])
            ->andReturnSelf();

        return $application;
    }
}
