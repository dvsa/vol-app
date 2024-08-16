<?php

/**
 * Continuation Not Sought List
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Continuation Not Sought List
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ContinuationNotSoughtList extends AbstractQuery
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * Gets the value of date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}
