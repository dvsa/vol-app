<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitSector;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitSectorQuota;
use Dvsa\Olcs\Transfer\Command\IrhpPermitSector\Update as UpdateSectorCmd;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitSectorQuota as IrhpPermitSectorQuotaRepo;

/**
 * Update an IRHP Permit Sector
 *
 * @author Scott Callaway
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitSectorQuota';

    /**
     * Handle Command
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /**
         * @var IrhpPermitSectorQuotaRepo $repo
         * @var UpdateSectorCmd           $command
         */
        $repo = $this->getRepo('IrhpPermitSectorQuota');

        $quotas = $command->getSectors();
        $ids = array_keys($quotas);

        $records = $repo->fetchByIds($ids);

        /** @var IrhpPermitSectorQuota $record */
        foreach ($records as $record) {
            $recordId = $record->getId();
            $updatedNumber = $quotas[$recordId];
            $record->update($updatedNumber);

            $repo->save($record);
            $this->result->addId('IrhpPermitSectorQuota', $recordId, true);
        }

        $this->result->addMessage('Irhp Permit Sector Quota updated');

        return $this->result;
    }
}
