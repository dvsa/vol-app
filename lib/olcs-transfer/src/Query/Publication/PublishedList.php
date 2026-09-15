<?php

namespace Dvsa\Olcs\Transfer\Query\Publication;

use DateTime;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * @Transfer\RouteName("backend/publication/published-list")
 */
class PublishedList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var string
     *
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional()
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"A&D", "N&P"}})
     */
    protected $pubType;

    /**
     * @var DateTime|string in format "D-m-y H:i:s"
     *
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d H:i:s"})
     */
    protected $pubDateFrom;

    /**
     * @var DateTime|string in format "D-m-y H:i:s"
     *
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d H:i:s"})
     */
    protected $pubDateTo;

    /**
     * @var int|string
     *
     * @Transfer\Optional()
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"B","C","D","F","G","H","K","M","N"}})
     */
    protected $trafficArea;

    /**
     * @return string|null
     */
    public function getPubType()
    {
        return $this->pubType;
    }

    /**
     * Get the value of pubDateFrom
     *
     * @return DateTime|string in format "D-m-y H:i:s"
     */
    public function getPubDateFrom()
    {
        return $this->pubDateFrom;
    }

    /**
     * Get the value of pubDateTo
     *
     * @return DateTime|string in format "D-m-y H:i:s"
     */
    public function getPubDateTo()
    {
        return $this->pubDateTo;
    }

    /**
     * Get the value of trafficArea
     *
     * @return int|string
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }
}
