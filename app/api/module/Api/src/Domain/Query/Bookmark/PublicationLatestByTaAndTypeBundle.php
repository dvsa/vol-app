<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * PublicationLatestByTaAndType Bundle
 */
class PublicationLatestByTaAndTypeBundle extends AbstractQuery
{
    protected $pubType;
    protected $trafficArea;
    protected $bundle = [];

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
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
