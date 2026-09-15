<?php

/**
 * Delete IRHP Candidate Permit
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/irhp-candidate-permits/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
