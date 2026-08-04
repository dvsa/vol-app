<?php

namespace Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class EnvironmentalComplaintList
 * @Transfer\RouteName("backend/environmental-complaint")
 */
class EnvironmentalComplaintList extends AbstractQuery
{
    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licence;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $case;

    /**
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Validator("Laminas\Validator\Identical", options={"token": false})
     */
    protected $isCompliance = false;

    /**
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @return int
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @return bool
     */
    public function getIsCompliance()
    {
        return $this->isCompliance;
    }
}
