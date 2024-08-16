<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Reset IrfoPsvAuth
 */
final class ResetIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrfoPsvAuth';

    /**
     * Handle Reset command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var IrfoPsvAuth $irfoPsvAuth */
        $irfoPsvAuth = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $newStatus = IrfoPsvAuth::STATUS_PENDING;
        if ($irfoPsvAuth->getStatus()->getId() === IrfoPsvAuth::STATUS_CNS) {
            $newStatus = IrfoPsvAuth::STATUS_RENEW;
        }

        $irfoPsvAuth->reset(
            $this->getRepo()->getRefdataReference($newStatus)
        );

        $this->getRepo()->save($irfoPsvAuth);

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());

        $result->addMessage('IRFO PSV Auth reset successfully');

        return $result;
    }
}
