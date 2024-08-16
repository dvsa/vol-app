<?php

/**
 * Not taken up applications list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Query\Application;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Not taken up applications list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class NotTakenUpList extends AbstractQuery
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
