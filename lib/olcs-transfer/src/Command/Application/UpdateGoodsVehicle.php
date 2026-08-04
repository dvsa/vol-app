<?php

/**
 * Update Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/named-single/goods-vehicles/single")
 * @Transfer\Method("PUT")
 */
final class UpdateGoodsVehicle extends AbstractCommand
{
    use Identity;
    use Version;
    use Application;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 999999})
     * @Transfer\Optional
     */
    protected $platedWeight;

    /**
     * @Transfer\Optional
     */
    protected $receivedDate;

    /**
     * @Transfer\Optional
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
    public function getRemovalDate()
    {
        return $this->removalDate;
    }
}
