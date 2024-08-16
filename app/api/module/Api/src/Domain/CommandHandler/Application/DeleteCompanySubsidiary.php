<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteCompanySubsidiary extends AbstractCompanySubsidiary
{
    /**
     * handler for command
     *
     * @param \Dvsa\Olcs\Transfer\Command\Application\DeleteCompanySubsidiary $command Delete command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        //  delete subsidiary
        $this->result = $this->delete($command);

        //  update application completion
        $this->result->merge(
            $this->updateApplicationCompetition($command->getApplication(), true)
        );

        return $this->result;
    }
}
