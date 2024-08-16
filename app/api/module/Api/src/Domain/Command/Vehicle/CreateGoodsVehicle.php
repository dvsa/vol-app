<?php

/**
 * Create Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Vehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\Vrm;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateGoodsVehicle extends AbstractCommand
{
    use Licence;
    use Vrm;

    protected $platedWeight;

    protected $specifiedDate;

    protected $receivedDate;

    protected $confirm;

    protected $identifyDuplicates = false;

    /** @var int  */
    protected $applicationId = null;

    /**
     * @return mixed
     */
    public function getPlatedWeight()
    {
        return $this->platedWeight;
    }

    /**
     * @return mixed
     */
    public function getSpecifiedDate()
    {
        return $this->specifiedDate;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * @return boolean
     */
    public function getIdentifyDuplicates()
    {
        return $this->identifyDuplicates;
    }

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
