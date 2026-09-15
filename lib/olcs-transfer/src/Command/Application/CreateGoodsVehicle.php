<?php

/**
 * Create Goods Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\UnvalidatedVrm;
use Dvsa\Olcs\Transfer\FieldType\Traits\Vrm;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/goods-vehicles")
 * @Transfer\Method("POST")
 */
final class CreateGoodsVehicle extends AbstractCommand
{
    use Identity;
    use Vrm;
    use UnvalidatedVrm;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 999999})
     */
    protected $platedWeight;

    /**
     * @Transfer\Optional
     */
    protected $receivedDate;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $confirm;

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
    public function getConfirm()
    {
        return $this->confirm;
    }
}
