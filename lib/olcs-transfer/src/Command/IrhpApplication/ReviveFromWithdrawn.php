<?php

/**
 * Revive IRHP Application from withdrawn state
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-application/revive-from-withdrawn")
 * @Transfer\Method("PUT")
 */
final class ReviveFromWithdrawn extends AbstractCommand
{
    use Identity;
}
