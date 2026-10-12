<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bus;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
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
     */
    #[Transfer\Filter("Laminas\Filter\Digits")]
    #[Transfer\Validator("Laminas\Validator\Digits")]
    #[Transfer\Validator("Laminas\Validator\GreaterThan", options: ["min" => 0])]
    #[Transfer\Optional]
    protected $routeNo;

    /**
     * @var int
     */
    #[Transfer\Filter("Laminas\Filter\Digits")]
    #[Transfer\Validator("Laminas\Validator\Digits")]
    #[Transfer\Validator("Laminas\Validator\GreaterThan", options: ["min" => 0])]
    protected $licenceId;

    #[Transfer\ArrayInput]
    #[Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")]
    #[Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")]
    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    #[Transfer\Validator("Laminas\Validator\InArray", options: ["haystack" => [
        BusReg::STATUS_ADMIN,BusReg::STATUS_CANCEL, BusReg::STATUS_CANCELLED, BusReg::STATUS_CNS,
        BusReg::STATUS_EXPIRED, BusReg::STATUS_NEW, BusReg::STATUS_REFUSED, BusReg::STATUS_REGISTERED,
        BusReg::STATUS_VAR, BusReg::STATUS_WITHDRAWN, "breg_s_surr", "breg_s_revoked", "breg_s_curt"
    ]])]
    #[Transfer\Optional]
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
