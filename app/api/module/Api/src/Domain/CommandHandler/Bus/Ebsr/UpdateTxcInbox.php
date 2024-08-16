<?php

/**
 * Update TxcInbox
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\UpdateTxcInbox as UpdateTxcInboxCmd;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Update TxcInbox
 */
final class UpdateTxcInbox extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'TxcInbox';

    /**
     * Handle command
     *
     * @param CommandInterface $command Command to be handled
     *
     * @return Result
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var UpdateTxcInboxCmd $command */

        $result = new Result();

        $currentUser = $this->getCurrentUser();

        $localAuthority = $currentUser->getLocalAuthority();

        if (!$localAuthority instanceof LocalAuthority) {
            throw new ForbiddenException('User not a local authority');
        }

        $txcInboxRecords = $this->getRepo()->fetchByIdsForLocalAuthority(
            $command->getIds(),
            $localAuthority->getId(),
            Query::HYDRATE_OBJECT
        );

        $count = 0;
        /** @var TxcInbox $txcInbox */
        foreach ($txcInboxRecords as $txcInbox) {
            $txcInbox->setFileRead('Y');
            $this->getRepo()->save($txcInbox);
            $count++;
        }

        $result->addMessage($count . ' records updated');

        return $result;
    }
}
