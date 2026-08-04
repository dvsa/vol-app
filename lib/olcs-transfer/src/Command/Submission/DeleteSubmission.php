<?php

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/submission/single")
 * @Transfer\Method("DELETE")
 */
class DeleteSubmission extends AbstractDeleteCommand
{
    //
}
