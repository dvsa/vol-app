<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as CreateConditionUndertakingCmd;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Create small vehicle condition
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateSmallVehicleCondition extends AbstractCommandHandler
{
    protected $repoServiceName = 'ConditionUndertaking';

    protected $extraRepos = ['Application'];

    public const string SMALL_VEHICLE_UNERRTAKINGS_NOTES =
        'Small vehicles undertakings
(a) The operator will advise the Traffic Commissioner of the make, model and ' .
        'registration number of vehicles used under that licence, and will advise of any changes.
(b) In respect of any vehicle with eight or less passengers seats used under the licence, ' .
        'the operator will provide an audit trail to the Traffic Commissioner or any enforcement body on ' .
        'request, that demonstrates compliance with PSV requirements. This includes paperwork as to how in ' .
        'respect of any service separate fares were paid and one of the two conditions set out in Question 1 ' .
        'were met. Note this undertaking does not apply when the vehicle is being used ' .
        'under the provisions of Section 79A.
(c) Each small vehicle to be used under the licence will have a V5C registration certificate, and the ' .
        'operator must possess and produce, when asked to do so, a document confirming this.
(d) Each small vehicle will receive a full safety inspection (maximum every 10 weeks) in premises suitable for ' .
        'the vehicle to ensure that its roadworthiness is maintained. Records of all inspections must be kept in ' .
        'accordance with the Guide to Maintaining Roadworthiness.
(e) At no time will the small vehicle carry more than eight passengers.
(f) The operator will at all times comply with the legislation in respect of the charging of separate fares and ' .
        'retain 12 months’ evidence of this compliance for each journey.
(g) Drivers of small vehicles will carry with them documentary evidence that separate fares have ' .
        'been charged for the current journey.
(h) The operator will not use a vehicle that does not meet the ECWVTA standards, British construction ' .
        'and use requirements or the Road Vehicles Approval Regulations 2009 (as amended).
(i) The operator or driver will not break the alcohol laws.';

    /**
     * Handle command
     *
     * @param CommandInterface $command handle command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $application = $this->getRepo('Application')->fetchById($command->getApplicationId());
        $smallVehicles = [
            ApplicationEntity::PSV_VEHICLE_SIZE_SMALL,
            ApplicationEntity::PSV_VEHICLE_SIZE_BOTH
        ];
        $whichVehicleSizes = $application->getPsvWhichVehicleSizes();
        if ($whichVehicleSizes === null || !in_array($whichVehicleSizes->getId(), $smallVehicles)) {
            return $this->result;
        }
        $conditions = $this->getRepo('ConditionUndertaking')->fetchSmallVehilceUndertakings(
            $application->getLicence()->getId()
        );
        if (count($conditions) > 0) {
            return $this->result;
        }
        $data = [
            'attachedTo' => ConditionUndertaking::ATTACHED_TO_LICENCE,
            'type' => ConditionUndertaking::TYPE_UNDERTAKING,
            'notes' => self::SMALL_VEHICLE_UNERRTAKINGS_NOTES,
            'application' => $application->getId()
        ];
        return $this->handleSideEffect(CreateConditionUndertakingCmd::create($data));
    }
}
