<?php

namespace Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create small vehicle condition
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateSmallVehicleCondition extends AbstractCommand
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
