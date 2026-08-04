<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractReopenCommand;

/**
 * Concrete reopen class.
 *
 * @Transfer\RouteName("backend/cases/single/reopen")
 * @Transfer\Method("PUT")
 */
class ReopenCase extends AbstractReopenCommand
{
    //
}
