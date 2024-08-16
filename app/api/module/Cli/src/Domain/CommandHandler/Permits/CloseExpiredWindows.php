<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Cli\Domain\Command\Permits\CloseExpiredWindows as CloseExpiredWindowsCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitWindow\Close as CloseWindowCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Close expired windows
 */
class CloseExpiredWindows extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';

    /**
     * Handle command
     *
     * @param CloseExpiredWindowsCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $now = new \DateTime();
        $windowsToBeClosed = $this->getRepo()->fetchWindowsToBeClosed($now, $command->getSince());

        foreach ($windowsToBeClosed as $window) {
            $this->result->merge(
                $this->handleSideEffect(
                    CloseWindowCmd::create(
                        [
                            'id' => $window->getId(),
                        ]
                    )
                )
            );
        }

        $this->result->addMessage(
            count($windowsToBeClosed) ? 'Expired windows have been closed' : 'No expired windows found'
        );

        return $this->result;
    }
}
