<?php

/**
 * Designed to return true or false depending on whether a licence number exists in the system, irrespective of status
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/msi/valid-licence")
 */
class Exists extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $licNo;

    /**
     * @return mixed
     */
    public function getLicNo()
    {
        return $this->licNo;
    }
}
