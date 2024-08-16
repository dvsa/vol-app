<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * PreviousPublication
 */
class PreviousPublication extends AbstractQuery
{
    protected $trafficArea;
    protected $publicationNo;
    protected $pubType;
    protected $bundle = [];

    /**
     * @return mixed
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * @return mixed
     */
    public function getPubType()
    {
        return $this->pubType;
    }

    /**
     * @return mixed
     */
    public function getPublicationNo()
    {
        return $this->publicationNo;
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
