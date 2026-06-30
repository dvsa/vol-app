<?php

/**
 * Get a list of OtherLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\OtherLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/other-licence")
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
