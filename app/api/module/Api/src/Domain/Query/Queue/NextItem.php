<?php

namespace Dvsa\Olcs\Api\Domain\Query\Queue;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Next Item
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class NextItem extends AbstractQuery
{
    protected $includeTypes = [];

    protected $excludeTypes = [];

    /**
     * Get types to Include
     *
     * @return array
     */
    public function getIncludeTypes()
    {
        return $this->includeTypes;
    }

    /**
     * Get types to exclude
     *
     * @return array
     */
    public function getExcludeTypes()
    {
        return $this->excludeTypes;
    }
}
