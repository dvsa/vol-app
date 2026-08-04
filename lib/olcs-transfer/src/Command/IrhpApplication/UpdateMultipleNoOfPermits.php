<?php

/**
 * updateMultipleNoOfPermits
 *
 * @author Jonathan Thomas
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\MultipleNoOfPermits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-application/update-multiple-no-of-permits")
 * @Transfer\Method("PUT")
 */
class UpdateMultipleNoOfPermits extends AbstractCommand
{
    use Identity;
    use MultipleNoOfPermits;
}
