<?php

/**
 * Update an IRHP Candidate Permit
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitRange;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use IrhpPermitRange;
}
