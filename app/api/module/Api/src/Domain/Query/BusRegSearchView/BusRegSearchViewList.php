<?php

namespace Dvsa\Olcs\Api\Domain\Query\BusRegSearchView;

use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList as BusRegSearchViewTransfer;

/**
 * Class Bus Reg Search View List for Organisation or LA, optionally filtered by licence and bus reg status
 *
 * @Transfer\RouteName("backend/bus-reg-search-view-list")
 */
class BusRegSearchViewList extends BusRegSearchViewTransfer
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $localAuthorityId;

    /**
     * Get Local Authority Id
     *
     * @return int
     */
    public function getLocalAuthorityId()
    {
        return $this->localAuthorityId;
    }

    /**
     * Set Local Authority Id
     *
     * @param int $localAuthorityId Local Authority Id
     *
     * @return void
     */
    public function setLocalAuthorityId($localAuthorityId)
    {
        $this->localAuthorityId = $localAuthorityId;
    }
}
