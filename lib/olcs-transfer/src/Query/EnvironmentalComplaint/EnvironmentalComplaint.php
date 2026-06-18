<?php

namespace Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Compaint
 * @Transfer\RouteName("backend/environmental-complaint/single")
 */
class EnvironmentalComplaint extends AbstractQuery
{
    use Identity;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Validator("Laminas\Validator\Identical", options={"token": false})
     */
    protected $isCompliance = false;

    /**
     * @return bool
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }
}
