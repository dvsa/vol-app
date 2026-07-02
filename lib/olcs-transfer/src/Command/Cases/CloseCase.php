<?php

namespace Dvsa\Olcs\Transfer\Command\Cases;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCloseCommand;

/**
 * Concrete reopen class.
 *
 * @Transfer\RouteName("backend/cases/single/close")
 * @Transfer\Method("PUT")
 */
class CloseCase extends AbstractCloseCommand
{
    //
}
