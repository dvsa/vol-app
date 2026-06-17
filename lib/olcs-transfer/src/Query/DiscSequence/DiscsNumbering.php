<?php

/**
 * Get discs numbering information
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\DiscSequence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/disc-sequence/discs-numbering")
 */
final class DiscsNumbering extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    public $niFlag;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    protected $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"lcat_gv", "lcat_psv"}})
     * @Transfer\Optional
     */
    protected $operatorType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $discSequence;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    public $startNumberEntered;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    public $maxPages;

    /**
     * Get a NI flag
     *
     * @return string
     */
    public function getNiFlag()
    {
        return $this->niFlag;
    }

    /**
     * Get a licence type
     *
     * @return string
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * Get an operator type
     *
     * @return string
     */
    public function getOperatorType()
    {
        return $this->operatorType;
    }

    /**
     * Get a disc sequence
     *
     * @return int
     */
    public function getDiscSequence()
    {
        return $this->discSequence;
    }

    /**
     * Get a start number
     *
     * @return int
     */
    public function getStartNumberEntered()
    {
        return $this->startNumberEntered;
    }

    /**
     * Get the value of maxPages
     *
     * @return mixed
     */
    public function getMaxPages()
    {
        return $this->maxPages;
    }
}
