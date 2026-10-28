<?php

namespace Dvsa\Olcs\Api\Domain\Query\BusRegSearchView;

use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList as BusRegSearchViewTransfer;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Class Bus Reg Search View List for Organisation or LA, optionally filtered by licence and bus reg status
 */
#[Transfer\RouteName("backend/bus-reg-search-view-list")]
class BusRegSearchViewList extends BusRegSearchViewTransfer
{
    /**
     * @var int
     */
    #[Transfer\Optional]
    #[Transfer\Filter("Laminas\Filter\Digits")]
    #[Transfer\Validator("Laminas\Validator\Digits")]
    #[Transfer\Validator("Laminas\Validator\GreaterThan", options: ["min" => 0])]
    protected $localAuthorityId;

    /**
     * Get Local Authority Id: an id, or a local authority's id with those of the authorities it replaced
     *
     * @return int|int[]
     */
    public function getLocalAuthorityId()
    {
        return $this->localAuthorityId;
    }

    /**
     * Set Local Authority Id
     *
     * @param int|int[] $localAuthorityId Local Authority Id(s)
     *
     * @return void
     */
    public function setLocalAuthorityId($localAuthorityId)
    {
        $this->localAuthorityId = $localAuthorityId;
    }
}
