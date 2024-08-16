<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Abstract Bundle
 */
class AbstractBundle extends AbstractQuery
{
    protected $bundle = [];

    /**
     * @return array
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}
