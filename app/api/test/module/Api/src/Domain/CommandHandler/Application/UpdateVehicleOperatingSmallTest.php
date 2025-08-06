<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateVehicleOperatingSmall as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleOperatingSmall as Command;
use Mockery as m;

class UpdateVehicleOperatingSmallTest extends AbstractUpdateApplicationTest
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'vehicle operating small updated';
    protected array $commandData = [
        'psvOperateSmallVhl' => 'Y',
    ];
    protected array $sections = [
        'psvOperateSmall',
    ];

    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('setPsvOperateSmallVhl')
            ->with($this->commandData['psvOperateSmallVhl'])
            ->andReturnSelf();

        return $application;
    }
}
