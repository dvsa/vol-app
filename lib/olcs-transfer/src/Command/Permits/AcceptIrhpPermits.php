<?php

/**
 * Accept IRHP awarded/granted permits
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/permits/irhp-permits-accept")
 * @Transfer\Method("POST")
 */
final class AcceptIrhpPermits extends AbstractCommand
{
    use Identity;
}
