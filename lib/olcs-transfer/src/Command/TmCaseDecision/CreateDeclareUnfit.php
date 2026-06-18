<?php

/**
 * Create DeclareUnfit
 */

namespace Dvsa\Olcs\Transfer\Command\TmCaseDecision;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/tm-case-decision/declare-unfit")
 * @Transfer\Method("POST")
 */
final class CreateDeclareUnfit extends AbstractCommand
{
    use FieldType\Cases;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isMsi;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $decisionDate;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $notifiedDate = null;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $unfitnessStartDate;

    /**
     * @Transfer\Optional()
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $unfitnessEndDate = null;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"tm_unfit_inc", "tm_unfit_inn"}})
     */
    protected $unfitnessReasons = [];

    /**
     * @Transfer\Optional()
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"tm_rehab_adc", "tm_rehab_adt", "tm_rehab_oth", "tm_rehab_rlc", "tm_rehab_rpt"}})
     */
    protected $rehabMeasures = [];

    /**
     * @return string
     */
    public function getIsMsi()
    {
        return $this->isMsi;
    }

    /**
     * @return string
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * @return string
     */
    public function getNotifiedDate()
    {
        return $this->notifiedDate;
    }

    /**
     * @return string
     */
    public function getUnfitnessStartDate()
    {
        return $this->unfitnessStartDate;
    }

    /**
     * @return string
     */
    public function getUnfitnessEndDate()
    {
        return $this->unfitnessEndDate;
    }

    /**
     * @return array
     */
    public function getUnfitnessReasons()
    {
        return $this->unfitnessReasons;
    }

    /**
     * @return array
     */
    public function getRehabMeasures()
    {
        return $this->rehabMeasures;
    }
}
