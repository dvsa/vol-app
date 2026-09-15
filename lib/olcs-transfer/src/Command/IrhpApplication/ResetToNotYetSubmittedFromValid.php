<?php

/**
 * Reset to NotYetSubmitted from Valid
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-application/reset-to-not-yet-submitted-from-valid")
 * @Transfer\Method("PUT")
 */
final class ResetToNotYetSubmittedFromValid extends AbstractCommand
{
    use Identity;
}
