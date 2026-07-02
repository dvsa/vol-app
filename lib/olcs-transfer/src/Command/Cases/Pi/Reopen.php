<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractReopenCommand;

/**
 * Concrete reopen class.
 *
 * @Transfer\RouteName("backend/pi/single/reopen")
 * @Transfer\Method("PUT")
 */
class Reopen extends AbstractReopenCommand
{
    //
}
