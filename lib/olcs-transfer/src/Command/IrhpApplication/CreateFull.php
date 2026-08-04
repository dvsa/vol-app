<?php

/**
 * Create Irhp Application
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\DateReceived;
use Dvsa\Olcs\Transfer\FieldType\Traits\Declaration;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\MultipleNoOfPermitsOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-application/full")
 * @Transfer\Method("POST")
 */
final class CreateFull extends AbstractCommand
{
    use Licence;
    use IrhpPermitType;
    use MultipleNoOfPermitsOptional;
    use DateReceived;
    use Declaration;
}
