<?php

/**
 * Approve Irfo Gv Permit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Approve Irfo Gv Permit
 */
final class ApproveIrfoGvPermit extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrfoGvPermit';

    protected $extraRepos = ['Fee'];

    public function handleCommand(CommandInterface $command)
    {
        $irfoGvPermit = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $irfoGvPermit->approve(
            $this->getRepo()->getRefdataReference(Entity::STATUS_APPROVED),
            $this->getRepo('Fee')->fetchFeesByIrfoGvPermitId($command->getId())
        );

        $this->getRepo()->save($irfoGvPermit);

        $result = new Result();
        $result->addId('irfoGvPermit', $irfoGvPermit->getId());
        $result->addMessage('IRFO GV Permit approved successfully');

        return $result;
    }
}
