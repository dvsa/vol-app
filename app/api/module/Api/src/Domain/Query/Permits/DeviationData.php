<?php

/**
 * Deviation data
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;

class DeviationData extends AbstractQuery
{
    /**
     * @Transfer\ArrayInput
     */
    protected $sourceValues;

    /**
     * Get source values
     *
     * @return array
     */
    public function getSourceValues()
    {
        return $this->sourceValues;
    }
}
