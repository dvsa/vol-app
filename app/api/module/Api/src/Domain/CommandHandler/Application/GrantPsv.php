<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CopyApplicationDataToLicence;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot as CreateSnapshotCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask as CloseTexTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask as CloseFeeDueTaskCmd;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateSmallVehicleCondition as CreateSvConditionUndertakingCmd;

/**
 * Grant Psv
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class GrantPsv extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ConditionUndertaking'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command);

        $result->merge($this->maybeCreateSmallVehicleCondition($application));

        $result->merge($this->createSnapshot($application->getId()));

        $this->updateStatusAndDate($application, ApplicationEntity::APPLICATION_STATUS_VALID);
        $result->addMessage('Application status updated');

        $this->updateStatusAndDate($application->getLicence(), Licence::LICENCE_STATUS_VALID);
        $result->addMessage('Licence status updated');

        $this->getRepo()->save($application);

        // Get licence totAuthVehicles before we update
        $currentTotAuth = $application->getLicence()->getTotAuthVehicles();

        $result->merge($this->proxyCommandAsSystemUser($command, CopyApplicationDataToLicence::class));

        $data = $command->getArrayCopy();
        $data['currentTotAuth'] = $currentTotAuth;

        $result->merge($this->handleSideEffectAsSystemUser(CreateDiscRecords::create($data)));

        if (!$application->isSpecialRestricted()) {
            $result->merge($this->proxyCommandAsSystemUser($command, ProcessApplicationOperatingCentres::class));
        }

        // If Internal user grants PSV application or variation
        if ($this->isInternalUser()) {
            $result->merge(
                $this->handleSideEffectAsSystemUser(CloseTexTaskCmd::create(['id' => $application->getId()]))
            );
            $result->merge(
                $this->handleSideEffectAsSystemUser(CloseFeeDueTaskCmd::create(['id' => $application->getId()]))
            );
        }

        $result->merge($this->proxyCommandAsSystemUser($command, CommonGrant::class));

        return $result;
    }

    protected function createSnapshot($applicationId)
    {
        $data = [
            'id' => $applicationId,
            'event' => CreateSnapshotCmd::ON_GRANT
        ];

        return $this->handleSideEffectAsSystemUser(CreateSnapshotCmd::create($data));
    }

    /**
     * @param ApplicationEntity|Licence $entity
     * @param $status
     */
    protected function updateStatusAndDate($entity, $status)
    {
        $entity->setStatus($this->getRepo()->getRefdataReference($status));
        $entity->setGrantedDate(new DateTime());
    }

    /**
     * Maybe create small vehicle condition
     *
     * @param ApplicationEntity $application application
     *
     * @return Result
     */
    protected function maybeCreateSmallVehicleCondition($application)
    {
        return $this->handleSideEffectAsSystemUser(
            CreateSvConditionUndertakingCmd::create(
                ['applicationId' => $application->getId()]
            )
        );
    }
}
