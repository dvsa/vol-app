<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateNoveltyVehicles as Handler;
use Dvsa\Olcs\Transfer\Command\Application\UpdateNoveltyVehicles as Command;
use Mockery as m;

class UpdateNoveltyVehiclesTest extends AbstractUpdateApplicationTestCase
{
    protected string $handlerClass = Handler::class;
    protected string $commandClass = Command::class;
    protected string $confirmationMessage = 'novelty vehicles updated';
    protected array $commandData = [
        'psvLimousines' => 'Y',
        'psvNoLimousineConfirmation' => 'N',
        'psvOnlyLimousinesConfirmation' => 'Y',
    ];
    protected array $sections = [
        'psvOperateNovelty',
    ];

    #[\Override]
    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        $application = parent::setupApplication();
        $application->expects('updatePsvNoveltyVehicles')
            ->with(
                $this->commandData['psvLimousines'],
                $this->commandData['psvNoLimousineConfirmation'],
                $this->commandData['psvOnlyLimousinesConfirmation']
            )
            ->andReturnSelf();

        return $application;
    }
}
