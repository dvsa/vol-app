<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteCompanySubsidiary extends AbstractCompanySubsidiary implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * handler for command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Licence\DeleteCompanySubsidiary $command Delete command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        //  create task
        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            /** @var \Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary[] $entities */
            $entities = $this->repo->fetchByIds($command->getIds());

            foreach ($entities as $entity) {
                $this->result->merge(
                    $this->createTask($command->getLicence(), 'Subsidiary company removed - ' . $entity->getName())
                );
            }
        }

        //  delete subsidiary
        $this->result->merge(
            $this->delete($command)
        );

        return $this->result;
    }
}
