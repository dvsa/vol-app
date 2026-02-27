<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateVehicleNinePassengers as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleNinePassengers as Command;
use Mockery as m;

class UpdateVehicleNinePassengersTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'vehicle nine passengers updated';
    protected array $commandData = [
        'psvNoSmallVhlConfirmation' => 'Y',
    ];
    protected array $sections = [
        'psvOperateLarge',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('setPsvNoSmallVhlConfirmation')
            ->with($this->commandData['psvNoSmallVhlConfirmation'])
            ->andReturnSelf();

        return $application;
    }
}
