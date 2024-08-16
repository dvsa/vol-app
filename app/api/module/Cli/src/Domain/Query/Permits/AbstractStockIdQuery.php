<?php

namespace Dvsa\Olcs\Cli\Domain\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Abstract stock id query
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AbstractStockIdQuery extends AbstractQuery
{
    /**
     * @var int
     */
    protected $stockId;

    /**
     * Gets the value of stockId
     *
     * @return int
     */
    public function getStockId()
    {
        return $this->stockId;
    }
}
