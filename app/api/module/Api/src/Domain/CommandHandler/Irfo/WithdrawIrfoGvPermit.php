<?php

/**
 * Withdraw Irfo Gv Permit
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelIrfoGvPermitFees as CancelFeesDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Withdraw Irfo Gv Permit
 */
final class WithdrawIrfoGvPermit extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrfoGvPermit';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $irfoGvPermit = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        // withdraw
        $irfoGvPermit->withdraw(
            $this->getRepo()->getRefdataReference(Entity::STATUS_WITHDRAWN)
        );

        $this->getRepo()->save($irfoGvPermit);

        $result->addId('irfoGvPermit', $irfoGvPermit->getId());

        // cancel all associated fees
        $result->merge($this->cancelFees($command));

        $result->addMessage('IRFO GV Permit withdrawn successfully');

        return $result;
    }

    private function cancelFees(CommandInterface $command)
    {
        return $this->handleSideEffect(
            CancelFeesDto::create(
                [
                    'id' => $command->getId(),
                ]
            )
        );
    }
}
