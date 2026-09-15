<?php

namespace Dvsa\Olcs\Transfer\Query\BusRegSearchView;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Bus Reg List for Organisation or LA
 * @Transfer\RouteName("backend/bus-reg-search-view-list")
 */
class BusRegSearchViewList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $organisationId;

    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $licId;

    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *          "haystack": {
     *              "breg_s_admin","breg_s_cancellation","breg_s_cancelled","breg_s_cns","breg_s_curt","breg_s_expired",
     *              "breg_s_new","breg_s_refused","breg_s_registered","breg_s_revoked","breg_s_surr","breg_s_var",
     *              "breg_s_withdrawn"
     *          }
     *     }
     * )
     */
    protected $busRegStatus;

    /**
     * Get Licence Id
     *
     * @return int
     */
    public function getLicId()
    {
        return $this->licId;
    }

    /**
     * Get Organisation Id
     *
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * Get Bus registration status
     *
     * @return String
     */
    public function getBusRegStatus()
    {
        return $this->busRegStatus;
    }
}
