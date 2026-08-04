<?php

namespace Dvsa\Olcs\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreaOptional;

/**
 * Class GetLatestFeeType
 * @Transfer\RouteName("backend/fee-type/latest")
 */
class GetLatestFeeType extends AbstractQuery
{
    use TrafficAreaOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1,"max":32})
     */
    protected $feeType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"lcat_gv","lcat_psv"}})
     */
    protected $operatorType;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    protected $licenceType;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $date;

    /**
     * @return mixed
     */
    public function getFeeType()
    {
        return $this->feeType;
    }

    /**
     * @return mixed
     */
    public function getOperatorType()
    {
        return $this->operatorType;
    }

    /**
     * @return mixed
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }
}
