<?php

namespace Dvsa\Olcs\Transfer\Query\CompaniesHouse;

use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreasOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class AlertList
 * @Transfer\RouteName("backend/companies-house-alert")
 */
class AlertList extends AbstractQuery implements
    PagedQueryInterface,
    OrderedQueryInterface,
    CacheableShortTermQueryInterface
{
    use PagedTraitOptional;
    use OrderedTraitOptional;
    use TrafficAreasOptional;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $includeClosed = false;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "company_status_change",
     *              "company_name_change",
     *              "company_address_change",
     *              "company_people_change",
     *              "invalid_company_number"
     *          }
     *      }
     * )
     */
    protected $typeOfChange;

    /**
     * Gets the value of includeClosed.
     *
     * @return mixed
     */
    public function getIncludeClosed()
    {
        return $this->includeClosed;
    }

    /**
     * Gets the value of typeOfChange.
     *
     * @return mixed
     */
    public function getTypeOfChange()
    {
        return $this->typeOfChange;
    }
}
