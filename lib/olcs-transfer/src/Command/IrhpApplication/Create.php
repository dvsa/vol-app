<?php

/**
 * Create Irhp Application
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EcmtApplicationAllOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\FromInternal;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-application")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use Licence;
    use IrhpPermitType;
    use FromInternal;
    use IrhpPermitStockOptional;
}
