<?php

/**
 * Update Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as CreateApplicationFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Variation\ResetVariation as ResetVariationCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DerivedTypeOfLicenceParamsTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Util\EntityCloner;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateTypeOfLicence as Cmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTypeOfLicence extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;
    use DerivedTypeOfLicenceParamsTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Application $licence */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $licence = $application->getLicence();

        if (!$this->isUpdateRequired($application, $command)) {
            $result->addMessage('No updates required');
            return $result;
        }

        if (!$this->isGranted(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)) {
            throw new ForbiddenException('You do not have permission to update type of licence');
        }

        if (
            !$licence->canBecomeSpecialRestricted()
            && $command->getLicenceType() === Licence::LICENCE_TYPE_SPECIAL_RESTRICTED
        ) {
            throw new ValidationException(
                [
                    'licenceType' => [
                        Licence::ERROR_CANT_BE_SR => 'You are not able to change licence type to special restricted'
                    ]
                ]
            );
        }

        if ($this->changeRequiresConfirmation($application, $command)) {
            return $this->handleSideEffect(
                $this->createResetVariationCommand($command)
            );
        }

        $derivedVehicleType = $this->getDerivedVehicleType(
            $command->getVehicleType(),
            $application->getGoodsOrPsv()->getId(),
        );

        $application->updateTypeOfLicence(
            $application->getNiFlag(),
            $application->getGoodsOrPsv(),
            $this->getRepo()->getRefdataReference($command->getLicenceType()),
            $this->getRepo()->getRefdataReference($derivedVehicleType),
            $command->getLgvDeclarationConfirmation()
        );

        $this->getRepo()->save($application);
        $result->addMessage('Application saved successfully');

        // update application completion - typeOfLicence section
        $result->merge($this->updateApplicationCompletion($command->getId(), 'typeOfLicence'));

        // update operating centres section if the application has changed vehicle or trailer authorisation
        $updateOperatingCentresSection = $application->hasAuthChanged();

        // check if the new type of licence requires OCs to be removed
        if (
            !$application->canHaveOperatingCentre()
            && (!$application->getOperatingCentres()->isEmpty() || !$licence->getOperatingCentres()->isEmpty())
        ) {
            // can't have OCs, remove them
            // delete all OCs already linked to this application
            foreach ($application->getOperatingCentres() as $aoc) {
                $this->getRepo('ApplicationOperatingCentre')->delete($aoc);
            }

            // for each and every OC linked to the related licence
            // create a record against the application which will remove it
            foreach ($licence->getOperatingCentres() as $loc) {
                /** @var ApplicationOperatingCentre $aoc */
                $aoc = EntityCloner::cloneEntityInto($loc, ApplicationOperatingCentre::class);
                $aoc->setAction(ApplicationOperatingCentre::ACTION_DELETE);
                $aoc->setApplication($application);

                $this->getRepo('ApplicationOperatingCentre')->save($aoc);
            }

            // update application completion - operatingCentres section
            $updateOperatingCentresSection = true;
        }

        if ($updateOperatingCentresSection) {
            $result->merge($this->updateApplicationCompletion($command->getId(), 'operatingCentres'));
        }

        // OLCS-10953: don't invoke fee logic if application was created internally
        if (!$application->createdInternally()) {
            $result->merge($this->handleVariationFees($application));
        }

        return $result;
    }

    /**
     * Do any changes need to be made to the variation?
     *
     * @param $command
     *
     * $return bool
     */
    private function isUpdateRequired(Application $application, $command)
    {
        $applicationLicenceType = (string)$application->getLicenceType();
        $commandLicenceType = $command->getLicenceType();

        $applicationVehicleType = (string)$application->getVehicleType();
        $commandVehicleType = $command->getVehicleType();

        if ($applicationLicenceType != $commandLicenceType) {
            return true;
        }

        if (
            $applicationLicenceType == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            $applicationVehicleType != $commandVehicleType
        ) {
            return true;
        }

        return false;
    }

    /**
     * Update application completion
     *
     * @param int $id
     * @param string $section
     *
     * @return Result
     */
    private function updateApplicationCompletion($id, $section)
    {
        return $this->handleSideEffect(
            UpdateApplicationCompletion::create(['id' => $id, 'section' => $section])
        );
    }

    private function handleVariationFees(Application $application)
    {
        $result = new Result();

        foreach ($application->getFees() as $fee) {
            if ($fee->isVariationFee() && $fee->isFullyOutstanding()) {
                // only cancel and recreate variation fee if it is fully outstanding
                $result->merge($this->cancelAndRecreateVariationFee($fee, $application));
            }
        }

        return $result;
    }

    /**
     * Cancel existing fee and create a new one
     */
    private function cancelAndRecreateVariationFee(Fee $fee, Application $application)
    {
        return $this->handleSideEffects(
            [
                CancelFeeCmd::create(['id' => $fee->getId()]),
                CreateApplicationFeeCmd::create(
                    [
                        'id' => $application->getId(),
                        'feeTypeFeeType' => FeeType::FEE_TYPE_VAR
                    ]
                )
            ]
        );
    }

    /**
     * Return a command prompting the frontend to reset the variation
     *
     *
     * @return ResetVariationCmd
     */
    private function createResetVariationCommand(Cmd $command): ResetVariationCmd
    {
        return ResetVariationCmd::create(
            $command->getArrayCopy()
        );
    }

    /**
     * Whether the changes require confirmation
     *
     *
     * @return bool
     */
    private function changeRequiresConfirmation(Application $application, Cmd $command): bool
    {
        // no confirmation required for internal users
        if ($this->isInternalUser()) {
            return false;
        }

        // no confirmation required for psv changes
        if ($application->getVehicleType() == RefData::APP_VEHICLE_TYPE_PSV) {
            return false;
        }

        // determine if the licence type or vehicle type of the variation has already been changed from the
        // corresponding values in the licence prior to this attempt
        $licence = $application->getLicence();
        $existingLicenceType = $licence->getLicenceType();
        $existingVehicleType = $licence->getVehicleType();

        $variationLicenceType = $application->getLicenceType();
        $variationVehicleType = $application->getVehicleType();

        if (($existingLicenceType == $variationLicenceType) && ($existingVehicleType == $variationVehicleType)) {
            return false;
        }

        return true;
    }
}
