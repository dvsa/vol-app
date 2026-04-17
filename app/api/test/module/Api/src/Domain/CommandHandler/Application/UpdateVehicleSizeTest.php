<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateVehicleSize as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleSize as Command;
use Mockery as m;

class UpdateVehicleSizeTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'vehicle size updated';
    protected array $commandData = [
        'psvVehicleSize' => 'psvvs_small',
    ];
    protected array $sections = [
        'vehiclesSize',
    ];

    protected $refData = [
        'psvvs_small',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('updatePsvVehicleSize')
            ->with($this->refData['psvvs_small'])
            ->andReturnSelf();

        return $application;
    }
}
