<?php

/**
 * Get a single Candidate Permit by Id
 *
 * @author Andy Newton  <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}
