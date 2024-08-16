<?php

namespace Dvsa\Olcs\Cli\Domain\Query\CommunityLic;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Get community licences list for activation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CommunityLicencesForActivationList extends AbstractQuery
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
