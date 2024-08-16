<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;

/**
 * VoidAllCommunityLicences
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class VoidAllCommunityLicences extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Api\Domain\Command\Licence\VoidAllCommunityLicences $command Command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licenceId = $command->getId();
        $this->getRepo()->expireAllForLicence($licenceId, CommunityLic::STATUS_ANNUL);
        $this->result->addMessage('All community licences voided');

        $updateTotalCommunityLicences =  UpdateTotalCommunityLicencesCommand::create(['id' => $licenceId]);
        $updateResult = $this->handleSideEffect($updateTotalCommunityLicences);
        $this->result->merge($updateResult);

        return $this->result;
    }
}
