<?php

/**
 * Reprint Disc
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Vehicle;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/goods-vehicles/disc/reprint")
 * @Transfer\Method("POST")
 */
final class ReprintDisc extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }
}
