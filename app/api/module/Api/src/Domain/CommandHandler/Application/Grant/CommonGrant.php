<?php

/**
 * Common Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\CancelAllInterimFees;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantCommunityLicence as GrantCommunityLicenceCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantConditionUndertaking as GrantConditionUndertakingCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantPeople as GrantPeopleCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantTransportManager as GrantTransportManagerCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessDuplicateVehicles as ProcessDuplicateVehiclesCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Licence\PrintLicence;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\Schedule41 as GrantSchedule41;

/**
 * Common Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CommonGrant extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);

        $result->merge($this->proxyCommand($command, CancelAllInterimFees::class));
        $result->merge($this->proxyCommand($command, GrantConditionUndertakingCmd::class));
        $result->merge($this->proxyCommand($command, GrantCommunityLicenceCmd::class));
        $result->merge($this->proxyCommand($command, GrantTransportManagerCmd::class));
        $result->merge($this->proxyCommand($command, GrantPeopleCmd::class));
        $result->merge($this->handleSideEffect(PrintLicence::create(['id' => $application->getLicence()->getId()])));

        if ($application->isGoods()) {
            $result->merge(
                $this->handleSideEffect(GrantSchedule41::create(['id' => $application->getId()]))
            );

            $result->merge($this->proxyCommand($command, ProcessDuplicateVehiclesCmd::class));
        }

        $this->tidyUpData($application);

        return $result;
    }

    protected function tidyUpData(ApplicationEntity $application)
    {
        // Tidy up some invalid data
        $licence = $application->getLicence();

        if ($licence->isRestricted()) {
            $licence->setEstablishmentCd(null);

            $this->getRepo()->save($application);
        }
    }
}
