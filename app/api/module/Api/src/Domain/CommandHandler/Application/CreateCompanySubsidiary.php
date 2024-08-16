<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateCompanySubsidiary extends AbstractCompanySubsidiary
{
    /**
     * Command Handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\CreateCompanySubsidiary $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $app */
        $app = $this->getRepo('Application')->fetchById($command->getApplication());

        //  create subsidiary
        $this->result = $this->create($command, $app->getLicence()->getId());

        //  update Application Completion
        $this->result->merge(
            $this->updateApplicationCompetition($command->getApplication(), true)
        );

        return $this->result;
    }
}
