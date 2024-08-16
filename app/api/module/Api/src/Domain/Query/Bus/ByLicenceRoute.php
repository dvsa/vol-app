<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bus;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class ByLicenceRoute
 */
class ByLicenceRoute extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $routeNo;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licenceId;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {
     *              "breg_s_admin", "breg_s_cancellation", "breg_s_cancelled", "breg_s_cns", "breg_s_curt",
     *              "breg_s_expired", "breg_s_new", "breg_s_refused", "breg_s_registered", "breg_s_revoked",
     *              "breg_s_surr", "breg_s_var", "breg_s_withdrawn"
     *         }
     *     }
     * )
     * @Transfer\Optional
     */
    protected $busRegStatus;

    /**
     * Gets routeNo
     *
     * @return int
     */
    public function getRouteNo()
    {
        return $this->routeNo;
    }

    /**
     * Gets licence id
     *
     * @return int
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }

    /**
     * Gets bus reg statuses
     *
     * @return array
     */
    public function getBusRegStatus()
    {
        return $this->busRegStatus;
    }
}
