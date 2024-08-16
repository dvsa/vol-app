<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateCompanySubsidiary extends AbstractCompanySubsidiary implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Command Handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\CreateCompanySubsidiary $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceId = $command->getLicence();

        //  create subsidiary
        $this->result = $this->create($command, $licenceId);

        //  create task
        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            $this->result->merge(
                $this->createTask($licenceId, 'Subsidiary company added - ' . $command->getName())
            );
        }

        return $this->result;
    }
}
