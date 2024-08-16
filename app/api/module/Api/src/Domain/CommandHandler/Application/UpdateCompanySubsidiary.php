<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateCompanySubsidiary extends AbstractCompanySubsidiary
{
    /**
     * Command Handler
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\UpdateCompanySubsidiary $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //  update subsidiary data
        $this->result = $this->update($command);

        //  update application completion
        $this->result->merge(
            $this->updateApplicationCompetition($command->getApplication(), $this->result->getFlag('hasChanged'))
        );

        return $this->result;
    }
}
