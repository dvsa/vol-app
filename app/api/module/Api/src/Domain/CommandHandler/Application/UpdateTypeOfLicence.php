<?php

/**
 * Update Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DerivedTypeOfLicenceParamsTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence as Cmd;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Command\Application\ResetApplication as ResetApplicationCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateApplicationFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber as GenerateLicenceNumberCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;
use Dvsa\Olcs\Api\Domain\Command\Licence\CancelLicenceFees;

/**
 * Update Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateTypeOfLicence extends AbstractCommandHandler implements TransactionedInterface
{
    use DerivedTypeOfLicenceParamsTrait;

    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // Early return if we haven't changed anything
        if (!$this->hasChangedTypeOfLicence($application, $command)) {
            $result->addMessage('No updates required');
            return $result;
        }

        if ($this->changeRequiresConfirmation($application, $command)) {
            return $this->handleSideEffect($this->createResetApplicationCommand($command));
        }

        $sideEffects = $this->determineSideEffects($application, $command);

        $derivedOperatorType = $this->getDerivedOperatorType(
            $command->getOperatorType(),
            $command->getNiFlag()
        );

        $derivedVehicleType = $this->getDerivedVehicleType(
            $command->getVehicleType(),
            $derivedOperatorType
        );

        $application->updateTypeOfLicence(
            $command->getNiFlag(),
            $this->getRepo()->getRefdataReference($derivedOperatorType),
            $this->getRepo()->getRefdataReference($command->getLicenceType()),
            $this->getRepo()->getRefdataReference($derivedVehicleType),
            $command->getLgvDeclarationConfirmation() ?? 0
        );

        $this->getRepo()->save($application);

        foreach ($sideEffects as $sideEffect) {
            $result->merge($this->handleSideEffect($sideEffect));
        }

        $result->addMessage('Application saved successfully');
        return $result;
    }

    private function determineSideEffects(Application $application, Cmd $command)
    {
        $sideEffects = [];

        if ($this->updatingForTheFirstTime($application)) {
            $sideEffects[] = $this->createCreateApplicationFeeCommand($application);
            $sideEffects[] = $this->createGenerateLicenceNumberCommand($application);
        } elseif (
            $this->licenceTypeWillChange($application, $command)
            && $this->applicationFeeNotPaid($application)
        ) {
            $sideEffects[] = $this->createCancelLicenceFeesCommand($application->getLicence());
            $sideEffects[] = $this->createCreateApplicationFeeCommand($application);
        }

        $sideEffects[] = $this->createUpdateApplicationCompletionCommand($application);

        return $sideEffects;
    }

    /**
     * Check whether we have changed anything
     *
     * @return bool
     */
    private function hasChangedTypeOfLicence(Application $application, Cmd $command)
    {
        $commandNiFlag = $command->getNiFlag();

        $derivedOperatorType = $this->getDerivedOperatorType(
            $command->getOperatorType(),
            $commandNiFlag
        );

        return $application->getNiFlag() !== $commandNiFlag
            || $application->getLicenceType() !== $this->getRepo()->getRefdataReference($command->getLicenceType())
            || (string)$application->getGoodsOrPsv() !== $derivedOperatorType
            || $application->getVehicleType() !== $this->getRepo()->getRefdataReference($command->getVehicleType());
    }

    private function createCreateApplicationFeeCommand(Application $application)
    {
        return CreateApplicationFeeCommand::create(['id' => $application->getId()]);
    }

    private function createCancelLicenceFeesCommand(Licence $licence)
    {
        return CancelLicenceFees::create(['id' => $licence->getId()]);
    }

    private function createGenerateLicenceNumberCommand(Application $application)
    {
        return GenerateLicenceNumberCommand::create(['id' => $application->getId()]);
    }

    private function createUpdateApplicationCompletionCommand(Application $application)
    {
        return UpdateApplicationCompletionCommand::create(
            ['id' => $application->getId(), 'section' => 'typeOfLicence']
        );
    }

    private function createResetApplicationCommand(Cmd $command)
    {
        $params = $command->getArrayCopy();

        $derivedOperatorType = $this->getDerivedOperatorType(
            $command->getOperatorType(),
            $command->getNiFlag()
        );

        $params['operatorType'] = $derivedOperatorType;

        $params['vehicleType'] = $this->getDerivedVehicleType(
            $command->getVehicleType(),
            $derivedOperatorType
        );

        return ResetApplicationCommand::create($params);
    }

    /**
     * Whether the changes require confirmation
     *
     * @return boolean
     */
    private function changeRequiresConfirmation(Application $application, Cmd $command)
    {
        return !$this->updatingForTheFirstTime($application) && (
            $this->typeOfLicenceWillChange($application, $command)
            || $this->changingToOrFromSr($application, $command)
            || $this->changingBetweenMixedAndLgv($application, $command)
            || $this->changingBetweenGoodsStandardInternationalAndOther($application, $command)
        );
    }

    /**
     * Whether we are changing to or from special restricted
     *
     * @return boolean
     */
    private function changingToOrFromSr(Application $application, Cmd $command)
    {
        $sr = $this->getRepo()->getRefdataReference(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED);
        $newLicenceType = $this->getRepo()->getRefdataReference($command->getLicenceType());

        return $this->licenceTypeWillChange($application, $command)
            && ($application->getLicenceType() === $sr || $newLicenceType === $sr);
    }

    /**
     * Whether we are changing between mixed and lgv vehicle type
     *
     *
     * @return boolean
     */
    private function changingBetweenMixedAndLgv(Application $application, Cmd $command)
    {
        $commandVehicleType = $command->getVehicleType();
        $applicationVehicleType = (string)$application->getVehicleType();

        if (
            $commandVehicleType == RefData::APP_VEHICLE_TYPE_LGV &&
            $applicationVehicleType == RefData::APP_VEHICLE_TYPE_MIXED
        ) {
            return true;
        }

        if (
            $commandVehicleType == RefData::APP_VEHICLE_TYPE_MIXED &&
            $applicationVehicleType == RefData::APP_VEHICLE_TYPE_LGV
        ) {
            return true;
        }

        return false;
    }

    /**
     * Whether we are changing between goods standard international and other licence type
     *
     *
     * @return boolean
     */
    private function changingBetweenGoodsStandardInternationalAndOther(Application $application, Cmd $command)
    {
        $goodsStandardInternationalVehicleTypes = [
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_MIXED,
        ];

        $commandVehicleType = $command->getVehicleType();
        $applicationVehicleType = (string)$application->getVehicleType();

        if (
            in_array($commandVehicleType, $goodsStandardInternationalVehicleTypes) &&
            !in_array($applicationVehicleType, $goodsStandardInternationalVehicleTypes)
        ) {
            return true;
        }

        if (
            in_array($applicationVehicleType, $goodsStandardInternationalVehicleTypes) &&
            !in_array($commandVehicleType, $goodsStandardInternationalVehicleTypes)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Whether the current TOL values are null
     *
     * @param Cmd $command
     * @return boolean
     */
    private function updatingForTheFirstTime(Application $application)
    {
        return $application->getNiFlag() === null
            || $application->getGoodsOrPsv() === null
            || $application->getLicenceType() === null;
    }

    /**
     * Whether we are changing niFlag or goodsOrPsv
     *
     * @return boolean
     */
    private function typeOfLicenceWillChange(Application $application, Cmd $command)
    {
        $commandNiFlag = $command->getNiFlag();

        $derivedOperatorType = $this->getDerivedOperatorType(
            $command->getOperatorType(),
            $commandNiFlag
        );

        return $application->getNiFlag() !== $commandNiFlag
            || (string)$application->getGoodsOrPsv() !== $derivedOperatorType;
    }

    /**
     * Whether we are changing licenceType
     *
     * @return boolean
     */
    private function licenceTypeWillChange(Application $application, Cmd $command)
    {
        return $application->getLicenceType() !== $this->getRepo()->getRefdataReference($command->getLicenceType());
    }

    /**
     * Check that the application does NOT have a paid or part-paid new
     * application fee (OLCS-10762)
     */
    private function applicationFeeNotPaid(Application $application)
    {
        foreach ($application->getFees() as $fee) {
            if ($fee->isNewApplicationFee()) {
                if ($fee->isPaid() || $fee->isPartPaid()) {
                    return false;
                }
            }
        }

        return true;
    }
}
