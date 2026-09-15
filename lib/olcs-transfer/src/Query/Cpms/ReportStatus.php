<?php

/**
 * Cpms Report Status
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Query\Cpms;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/cpms/report/named-single")
 */
class ReportStatus extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 1})
     */
    protected $reference;

    /**
     * Gets the value of reference.
     *
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }
}
