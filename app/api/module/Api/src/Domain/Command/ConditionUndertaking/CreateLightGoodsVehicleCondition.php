<?php

namespace Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create light goods vehicle condition
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class CreateLightGoodsVehicleCondition extends AbstractCommand
{
    /**
     * @var int
     */
    protected $applicationId;

    /**
     * Get application id
     *
     * @return int
     */
    public function getApplicationId()
    {
        return $this->applicationId;
    }
}
