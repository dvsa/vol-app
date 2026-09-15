<?php

/**
 * Create Operating Centre
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\ApplicationOperatingCentre\AbstractOperatingCentreCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsTaOverridden;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/named-single/operating-centre")
 * @Transfer\Method("POST")
 */
final class CreateOperatingCentre extends AbstractOperatingCentreCommand
{
    use Application;
    use IsTaOverridden;
}
