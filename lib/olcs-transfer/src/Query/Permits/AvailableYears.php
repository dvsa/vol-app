<?php

/**
 * Get list of all available permit years by permit type id
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/available-years")
 */
class AvailableYears extends AbstractQuery
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $type;

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
}
