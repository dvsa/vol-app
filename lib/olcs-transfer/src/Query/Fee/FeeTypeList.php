<?php

namespace Dvsa\Olcs\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\FieldType;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * Class FeeTypeList
 * @Transfer\RouteName("backend/fee-type")
 */
class FeeTypeList extends AbstractQuery implements
    FieldType\ApplicationInterface,
    FieldType\BusRegInterface,
    FieldType\LicenceInterface,
    FieldType\OrganisationInterface,
    CacheableLongTermQueryInterface,
    PublicQueryCacheInterface
{
    // Foreign Keys
    use FieldTypeTraits\ApplicationOptional;
    use FieldTypeTraits\BusRegOptional;
    use FieldTypeTraits\LicenceOptional;
    use FieldTypeTraits\OrganisationOptional;

    /**
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $isMiscellaneous;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    protected $effectiveDate;

    /**
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 1, "inclusive": true})
     * @Transfer\Optional
     */
    protected $currentFeeType;

    /**
     * @return int
     */
    public function getIsMiscellaneous()
    {
        return $this->isMiscellaneous;
    }

    /**
     * Gets the value of effectiveDate
     *
     * @return string
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * Gets the value of current fee type
     *
     * @return int
     */
    public function getCurrentFeeType()
    {
        return $this->currentFeeType;
    }
}
