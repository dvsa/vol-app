<?php

/**
 * Get a list of PerviousConvictions
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\PreviousConviction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/previous-conviction")
 */
final class GetList extends AbstractQuery
{
    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManager;

    /**
     * Get a Transport Manager ID
     *
     * @return int
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }
}
