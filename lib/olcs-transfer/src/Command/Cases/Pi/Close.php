<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCloseCommand;

/**
 * Concrete close class.
 *
 * @Transfer\RouteName("backend/pi/single/close")
 * @Transfer\Method("PUT")
 */
class Close extends AbstractCloseCommand
{
    //
}
