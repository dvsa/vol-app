<?php

namespace Dvsa\Olcs\Api\Domain\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * PSV licences to surrender list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvLicenceSurrenderList extends AbstractQuery
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
