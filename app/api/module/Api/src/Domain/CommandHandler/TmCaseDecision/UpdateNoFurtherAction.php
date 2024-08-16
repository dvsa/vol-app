<?php

/**
 * Update NoFurtherAction
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Update NoFurtherAction
 */
final class UpdateNoFurtherAction extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TmCaseDecision';

    public function handleCommand(CommandInterface $command)
    {
        $tmCaseDecision = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        if ($tmCaseDecision->getDecision()->getId() !== Entity::DECISION_NO_FURTHER_ACTION) {
            throw new BadRequestException('Invalid action');
        }

        $tmCaseDecision->update($command->getArrayCopy());

        $this->getRepo()->save($tmCaseDecision);

        $result = new Result();
        $result->addId('tmCaseDecision', $tmCaseDecision->getId());
        $result->addMessage('Decision updated successfully');

        return $result;
    }
}
