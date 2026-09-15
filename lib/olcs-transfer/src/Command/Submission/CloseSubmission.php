<?php

namespace Dvsa\Olcs\Transfer\Command\Submission;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCloseCommand;
use Dvsa\Olcs\Transfer\FieldType as FieldType;

/**
 * Concrete close class.
 *
 * @Transfer\RouteName("backend/submission/single/close")
 * @Transfer\Method("PUT")
 */
class CloseSubmission extends AbstractCloseCommand
{
    //
}
