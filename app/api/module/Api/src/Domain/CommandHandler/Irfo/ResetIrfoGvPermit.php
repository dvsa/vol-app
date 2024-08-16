<?php

/**
 * Reset Irfo Gv Permit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Reset Irfo Gv Permit
 */
final class ResetIrfoGvPermit extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrfoGvPermit';

    public function handleCommand(CommandInterface $command)
    {
        $irfoGvPermit = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $irfoGvPermit->reset(
            $this->getRepo()->getRefdataReference(Entity::STATUS_PENDING)
        );

        $this->getRepo()->save($irfoGvPermit);

        $result = new Result();
        $result->addId('irfoGvPermit', $irfoGvPermit->getId());
        $result->addMessage('IRFO GV Permit updated successfully');

        return $result;
    }
}
