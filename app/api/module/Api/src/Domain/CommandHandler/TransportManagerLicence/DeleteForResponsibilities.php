<?php

/**
 * Delete a Transport Manager Licence for TM Responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerLicence;

use Dvsa\Olcs\Api\Domain\Command\Licence\TmNominatedTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;

/**
 * Delete a Transport Manager Licence for TM Responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class DeleteForResponsibilities extends AbstractDeleteCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerLicence';

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();

        $tmls = $this->getRepo()->fetchByIds($ids);

        $licenceIds = [];

        /** @var TransportManagerLicence $tml */
        foreach ($tmls as $tml) {
            $licence = $tml->getLicence();
            $licenceIds[$licence->getId()] = $licence->getId();
        }

        $result = parent::handleCommand($command);

        $result->merge($this->handleSideEffect(TmNominatedTask::create(['ids' => $licenceIds])));

        return $result;
    }
}
