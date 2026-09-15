<?php

/**
 * Get list of IRHP Candidate Permits by IRHP Application id (unpaged)
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplication;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsPreGrant;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits/by-irhp-application/unpaged")
 */
class GetListByIrhpApplicationUnpaged extends AbstractQuery implements OrderedQueryInterface
{
    use IrhpApplication;
    use OrderedTrait;
    use IsPreGrant;

    /**
     * @var bool
     * @Transfer\Optional
     */
    protected $wantedOnly = false;

    /**
     * @return bool
     */
    public function getWantedOnly()
    {
        return $this->wantedOnly;
    }
}
