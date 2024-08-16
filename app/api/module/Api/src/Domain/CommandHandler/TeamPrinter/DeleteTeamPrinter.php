<?php

/**
 * Delete a team printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TeamPrinter;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete a team printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class DeleteTeamPrinter extends AbstractCommandHandler
{
    protected $repoServiceName = 'TeamPrinter';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $repo = $this->getRepo();
        $teamPrinter = $repo->fetchUsingId($command);
        $repo->delete($teamPrinter);
        $result->addMessage('TeamPrinter deleted');

        return $result;
    }
}
