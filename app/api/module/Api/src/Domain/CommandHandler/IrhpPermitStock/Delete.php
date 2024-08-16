<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Delete an IRHP Permit Stock
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class Delete extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitStock';
    protected $extraRepos = ['IrhpPermitJurisdictionQuota', 'IrhpPermitSectorQuota'];

    /**
     * Delete Command Handler
     *
     * @param CommandInterface $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();
        $stock = $this->getRepo()->fetchById($id);

        /** @var IrhpPermitStock $stock */
        if (!$stock->canDelete()) {
            throw new ValidationException(['irhp-permit-stock-cannot-delete-active-dependencies']);
        }

        // Remove the connection to the Jurisdiction Quotas
        foreach ($stock->getIrhpPermitJurisdictionQuotas() as $quota) {
            $this->getRepo('IrhpPermitJurisdictionQuota')->delete($quota);
        }

        // Remove the connection to the Sector Quotas
        foreach ($stock->getIrhpPermitSectorQuotas() as $quota) {
            $this->getRepo('IrhpPermitSectorQuota')->delete($quota);
        }

        try {
            $this->getRepo()->delete($stock);
            $this->result->addId('id', $id);
            $this->result->addMessage('Permit Stock Deleted');
        } catch (NotFoundException) {
            $this->result->addMessage(sprintf('Id %d not found', $id));
        }

        return $this->result;
    }
}
