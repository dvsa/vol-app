<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as CreateConditionUndertakingCmd;

/**
 * Create light goods vehicle condition
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class CreateLightGoodsVehicleCondition extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ConditionUndertaking'];

    /**
     * Handle command
     *
     * @param CommandInterface $command handle command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $applicationId = $command->getApplicationId();

        $application = $this->getRepo()->fetchById($applicationId);

        if ($application->getVehicleType()->getId() != RefData::APP_VEHICLE_TYPE_LGV) {
            return $this->result;
        }

        $hasUndertakings = $this->getRepo('ConditionUndertaking')->hasLightGoodsVehicleUndertakings(
            $application->getLicence()->getId()
        );

        if ($hasUndertakings) {
            return $this->result;
        }

        $data = [
            'attachedTo' => ConditionUndertaking::ATTACHED_TO_LICENCE,
            'conditionCategory' => ConditionUndertaking::CATEGORY_OTHER,
            'type' => ConditionUndertaking::TYPE_UNDERTAKING,
            'notes' => ConditionUndertaking::LIGHT_GOODS_VEHICLE_UNDERTAKINGS,
            'application' => $applicationId
        ];

        return $this->handleSideEffect(
            CreateConditionUndertakingCmd::create($data)
        );
    }
}
