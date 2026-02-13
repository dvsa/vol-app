<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as EndInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateVariationCompletion as UpdateVariationCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateSmallVehicleCondition as CreateSvConditionUndertakingCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\RefundInterimTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\BadVariationTypeException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs as CreatePsvDiscsCmd;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as Cmd;

/**
 * Grant
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Grant extends AbstractCommandHandler implements TransactionedInterface
{
    use RefundInterimTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['GoodsDisc', 'PsvDisc', 'LicenceVehicle'];

    /**
     * handleCommand
     *
     * @param  Cmd $command Command
     * @return Result       Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $command Cmd */
        $result = $this->result;

        /** @var ApplicationEntity $application */
        $application = $this->getRepo()->fetchUsingId($command);

        $this->guardAgainstBadVariationType($application);
        // Auto-complete tracking for auto-granted variations
        if ($application->getWasAutoGranted()) {
            $result->merge($this->autoCompleteTracking($application));
        }

        $licence = $application->getLicence();

        if ($application->isPsv()) {
            $this->maybeCreateSmallVehicleCondition($application);
        }

        $result->merge($this->createSnapshot($command->getId()));

        // this must be called before anything is changed as it needs to know the differences
        // between application and licence
        if ($application->isPublishable()) {
            $result->merge($this->publishApplication($application));
            $result->merge($this->closeTexTask($application));
        }

        // For auto-grants, update status as system user so change history shows "system"
        // For manual grants from internal, use the actual user who clicked grant
        if ($application->getWasAutoGranted()) {
            $this->getIdentityProvider()->setMasqueradedAsSystemUser(true);
        }

        $this->updateStatusAndDate($application, ApplicationEntity::APPLICATION_STATUS_VALID);
        $application->setGrantAuthority($this->refData($command->getGrantAuthority()));
        $this->getRepo()->save($application);

        if ($application->getWasAutoGranted()) {
            $this->getIdentityProvider()->setMasqueradedAsSystemUser(false);
        }

        if ($application->getCurrentInterimStatus() === ApplicationEntity::INTERIM_STATUS_REQUESTED) {
            $this->maybeRefundInterimFee($application);
        }

        if ($application->getLicenceType() !== $licence->getLicenceType()) {
            $this->updateExistingDiscs($application, $licence, $result);
        }

        $currentTotAuth = $licence->getTotAuthVehicles();

        $licence->copyInformationFromApplication($application);

        $data = $command->getArrayCopy();
        $data['currentTotAuth'] = $currentTotAuth;

        if ($application->isGoods()) {
            $this->checkForDuplicateVehicles($application);
        }
        $result->merge($this->handleSideEffectAsSystemUser(CreateDiscRecords::create($data)));

        $result->merge($this->proxyCommandAsSystemUser($command, ProcessApplicationOperatingCentres::class));
        $result->merge($this->proxyCommandAsSystemUser($command, CommonGrant::class));

        if (
            $application->isGoods() && $application->isVariation() &&
            $application->getCurrentInterimStatus() === ApplicationEntity::INTERIM_STATUS_INFORCE
        ) {
            $result->merge($this->handleSideEffectAsSystemUser(EndInterimCmd::create(['id' => $application->getId()])));
        }

        $result->addId('Application', $application->getId());
        $result->addMessage('Application ' . $application->getId() . ' granted');
        return $result;
    }

    /**
     * Auto-complete all tracking fields to "Accepted" for auto-granted variations
     *
     * @param ApplicationEntity $application
     * @return Result
     */
    protected function autoCompleteTracking(ApplicationEntity $application)
    {
        $result = new Result();

        try {
            // Get the application tracking entity
            $tracking = $application->getApplicationTracking();

            if ($tracking === null) {
                $result->addMessage('Warning: No tracking entity found');
                return $result;
            }

            // Define sections that can be auto-completed for variations
            $sections = [
                'addresses',
                'businessDetails',
                'businessType',
                'communityLicences',
                'conditionsUndertakings',
                'operatingCentres',
                'people',
                'safety',
                'transportManagers',
                'typeOfLicence',
                'declarationsInternal',
                'vehicles'
            ];

            // Update completion status for each section using UpdateVariationCompletion
            foreach ($sections as $section) {
                $updateCmd = UpdateVariationCompletionCmd::create([
                    'id' => $application->getId(),
                    'section' => $section,
                    'data' => ['status' => ApplicationTracking::STATUS_ACCEPTED]
                ]);
                $result->merge($this->handleSideEffectAsSystemUser($updateCmd));
            }
            $result->addMessage('Auto-completed tracking for auto-grant');

        } catch (\Exception $e) {
            $result->addMessage('Warning: Could not auto-complete tracking: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * createSnapshot
     *
     * @param int $applicationId Application ID
     * @return Result            Result
     */
    protected function createSnapshot($applicationId)
    {
        $data = [
            'id' => $applicationId,
            'event' => CreateSnapshot::ON_GRANT
        ];

        return $this->handleSideEffectAsSystemUser(CreateSnapshot::create($data));
    }

    /**
     * updateStatusAndDate
     *
     * @param ApplicationEntity|Licence $entity Entity
     * @param string                    $status Status
     *
     * @return void
     */
    protected function updateStatusAndDate($entity, $status)
    {
        $entity->setStatus($this->getRepo()->getRefdataReference($status));
        $entity->setGrantedDate(new DateTime());
    }

    /**
     * updateExistingDiscs
     *
     * @param ApplicationEntity $application Application
     * @param Licence           $licence     Licence
     * @param Result            $result      Result
     *
     * @return void
     */
    protected function updateExistingDiscs(ApplicationEntity $application, Licence $licence, Result $result)
    {
        $this->getIdentityProvider()->setMasqueradedAsSystemUser(true);
        if ($application->isGoods()) {
            $this->updateExistingGoodsDiscs($application, $result);
        } else {
            $this->updateExistingPsvDiscs($licence, $result);
        }
        $this->getIdentityProvider()->setMasqueradedAsSystemUser(false);
    }

    /**
     * updateExistingPsvDiscs
     *
     * @param Licence $licence Licence
     * @param Result  $result  Result
     *
     * @return void
     */
    protected function updateExistingPsvDiscs(Licence $licence, Result $result)
    {
        $discCount = $licence->getPsvDiscsNotCeased()->count();
        if ($discCount === 0) {
            return;
        }

        $this->getRepo('PsvDisc')->ceaseDiscsForLicence($licence->getId());

        $dtoData = [
            'licence' => $licence->getId(),
            'amount' => $discCount,
            'isCopy' => 'N'
        ];

        $result->merge(
            $this->handleSideEffectAsSystemUser(CreatePsvDiscsCmd::create($dtoData))
        );
    }

    /**
     * updateExistingGoodsDiscs
     *
     * @param ApplicationEntity $application Application
     * @param Result            $result      Result
     *
     * @return void
     */
    protected function updateExistingGoodsDiscs(ApplicationEntity $application, Result $result)
    {
        $count = $this->getRepo('GoodsDisc')->updateExistingGoodsDiscs($application);

        $result->addMessage($count . ' Goods Disc(s) replaced');
    }

    /**
     * Close any TEX tasks on the application
     *
     * @param ApplicationEntity $application Application
     *
     * @return Result
     */
    protected function closeTexTask(ApplicationEntity $application)
    {
        return $this->handleSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::create(
                [
                    'id' => $application->getId(),
                ]
            )
        );
    }

    /**
     * Publish the application
     *
     * @param ApplicationEntity $application Application
     *
     * @return Result
     */
    protected function publishApplication(ApplicationEntity $application)
    {
        return $this->handleSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::create(
                [
                    'id' => $application->getId(),
                    'trafficArea' => $application->getTrafficArea()->getId(),
                    'publicationSection' => \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::VAR_GRANTED_SECTION,
                ]
            )
        );
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

    /**
     * Remove vehicles on current application if we have same VRM vehicle specified on the licence
     *
     * @param ApplicationEntity $application application
     *
     * @return void
     */
    protected function checkForDuplicateVehicles($application)
    {
        $existingLicenceVehicles = $application->getLicence()->getLicenceVehicles();
        $newLicenceVehicles = $application->getLicenceVehicles();
        foreach ($newLicenceVehicles as $newLicenceVehicle) {
            $criteria = Criteria::create();
            $criteria->where(
                $criteria->expr()->eq('vehicle', $newLicenceVehicle->getVehicle())
            );
            $criteria->andWhere(
                $criteria->expr()->neq('specifiedDate', null)
            );
            $criteria->andWhere(
                $criteria->expr()->eq('removalDate', null)
            );
            $criteria->andWhere(
                $criteria->expr()->eq('interimApplication', null)
            );
            $sameVehicles = $existingLicenceVehicles->matching($criteria);
            if (count($sameVehicles) > 0) {
                $newLicenceVehicle->setRemovalDate(new DateTime());
                $this->getRepo('LicenceVehicle')->save($newLicenceVehicle);
            }
        }
    }

    /**
     * guardAgainstBadVariationType
     *
     * @param ApplicationEntity $application Application
     *
     * @throws BadVariationTypeException
     *
     * @return void
     */
    private function guardAgainstBadVariationType(ApplicationEntity $application)
    {
        if (!is_null($application->getVariationType())) {
            throw new BadVariationTypeException();
        }
    }
}
