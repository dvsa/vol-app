<?php

/**
 * Create Irhp Candidate Permit
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitApplication;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitRange;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use IrhpPermitRange;
    use IrhpPermitApplication;
}
