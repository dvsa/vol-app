<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Previous Hearing Bundle
 */
class PreviousHearingBundle extends AbstractQuery
{
    protected $pi;
    protected $hearingDate;
    protected $bundle = [];

    /**
     * @return mixed
     */
    public function getHearingDate()
    {
        return $this->hearingDate;
    }

    /**
     * @return mixed
     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
