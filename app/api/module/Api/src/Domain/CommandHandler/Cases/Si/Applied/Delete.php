<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Si\Applied;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete SiPenalty ("applied penalty" on the internal side)
 */
final class Delete extends AbstractCommandHandler
{
    protected $repoServiceName = 'SiPenalty';

    /**
     * Delete
     *
     * @param CommandInterface $command
     * @return Result
     * @throws Exception\ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var SiPenalty $penalty */
        $penalty = $this->getRepo()->fetchUsingId($command);

        if (!$penalty->getSeriousInfringement()->getCase()->isOpenErruCase()) {
            throw new Exception\ValidationException(['Invalid action for the case']);
        }

        $this->getRepo()->delete($penalty);

        $result = new Result();
        $result->addId('id', $penalty->getId());
        $result->addMessage('Applied penalty deleted');

        return $result;
    }
}
