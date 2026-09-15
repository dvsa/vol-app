<?php

/**
 * Update Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Vehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence-vehicle/single")
 * @Transfer\Method("PUT")
 */
final class UpdateGoodsVehicle extends AbstractCommand
{
    use Identity;
    use Version;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 999999})
     * @Transfer\Optional
     */
    protected $platedWeight;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $receivedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
     */
    protected $specifiedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $seedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $sentDate;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $removalDate;

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
    public function getReceivedDate()
    {
        return $this->receivedDate;
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
    public function getSeedDate()
    {
        return $this->seedDate;
    }

    /**
     * @return mixed
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * @return mixed
     */
    public function getRemovalDate()
    {
        return $this->removalDate;
    }
}
