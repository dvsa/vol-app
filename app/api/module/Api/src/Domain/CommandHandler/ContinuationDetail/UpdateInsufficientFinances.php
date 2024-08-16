<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\UpdateFinances as Command;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;

/**
 * Update Insufficient Finances part of ContinuationDetail
 */
final class UpdateInsufficientFinances extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * Handle command
     *
     * @param CommandInterface $command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Command */

        /* @var $continuationDetail ContinuationDetail */
        $continuationDetail = $this->getRepo()->fetchById(
            $command->getId(),
            \Doctrine\ORM\Query::HYDRATE_OBJECT,
            $command->getVersion()
        );

        $continuationDetail->setFinancialEvidenceUploaded($command->getFinancialEvidenceUploaded());

        $this->getRepo()->save($continuationDetail);

        $this->result->addId('continuationDetail', $continuationDetail->getId());
        $this->result->addMessage('ContinuationDetail insufficient finances updated');

        return $this->result;
    }
}
