<?php

/**
 * Licence Registered Address
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence/registered-address")
 */
class LicenceRegisteredAddress extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":2, "max":18})
     */
    protected $licenceNumber;

    /**
     * @return mixed
     */
    public function getLicenceNumber()
    {
        return $this->licenceNumber;
    }
}
