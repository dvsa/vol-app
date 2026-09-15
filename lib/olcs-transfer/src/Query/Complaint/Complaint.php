<?php

namespace Dvsa\Olcs\Transfer\Query\Complaint;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Compaint
 * @Transfer\RouteName("backend/complaint/single")
 */
class Complaint extends AbstractQuery
{
    use Identity;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Validator("Laminas\Validator\Identical", options={"token": true})
     */
    protected $isCompliance = true;

    /**
     * @return bool
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }
}
