<?php

/**
 * SubmitApplicationPath
 *
 * @author Jonathan Thomas
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\PostData;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-application/submit-application-path")
 * @Transfer\Method("PUT")
 */
class SubmitApplicationPath extends AbstractCommand
{
    use Identity;
    use PostData;
}
