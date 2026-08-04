<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Report;

use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreasOptionalWithOther;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/cases/report/open")
 */
class OpenList extends AbstractQuery implements PagedQueryInterface
{
    use PagedTrait;
    use TrafficAreasOptionalWithOther;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToLower")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\CaseType")
     */
    protected $caseType = null;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToLower")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\ApplicationStatus")
     */
    protected $applicationStatus = null;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\LicenceStatus")
     */
    protected $licenceStatus = null;

    /**
     * Get Case Type
     *
     * @return string
     */
    public function getCaseType()
    {
        return $this->caseType;
    }

    /**
     * Get Application Status
     *
     * @return string
     */
    public function getApplicationStatus()
    {
        return $this->applicationStatus;
    }

    /**
     * Get Licence Status
     *
     * @return string
     */
    public function getLicenceStatus()
    {
        return $this->licenceStatus;
    }
}
