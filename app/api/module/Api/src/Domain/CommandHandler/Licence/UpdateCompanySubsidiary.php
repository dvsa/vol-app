<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateCompanySubsidiary extends AbstractCompanySubsidiary implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Command Handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\UpdateCompanySubsidiary $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //  update company subsidiary details
        $this->result = $this->update($command);

        //  create task
        if (
            $this->result->getFlag('hasChanged') === true
            && $this->isGranted(Permission::SELFSERVE_USER)
        ) {
            $this->result->merge(
                $this->createTask($command->getLicence(), 'Subsidiary company updated - ' . $command->getName())
            );
        }

        return $this->result;
    }
}
