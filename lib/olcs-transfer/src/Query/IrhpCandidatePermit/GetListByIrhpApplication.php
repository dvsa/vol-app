<?php

/**
 * Get list of IRHP Candidate Permits by IRHP Application id
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits/by-irhp-application")
 */
class GetListByIrhpApplication extends GetListByIrhpApplicationUnpaged implements PagedQueryInterface
{
    use PagedTrait;
}
