<?php

/**
 * Get disc prefix
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\DiscSequence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/disc-sequence/disc-prefixes")
 */
final class DiscPrefixes extends AbstractQuery
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
}
