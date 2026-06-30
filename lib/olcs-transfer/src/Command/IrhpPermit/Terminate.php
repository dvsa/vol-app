<?php

/**
 * Terminate IRHP Permit
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-permits/single/terminate")
 * @Transfer\Method("POST")
 */
final class Terminate extends AbstractCommand
{
    use Identity;
}
