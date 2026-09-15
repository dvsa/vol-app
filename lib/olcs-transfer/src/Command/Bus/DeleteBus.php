<?php

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/bus/single")
 * @Transfer\Method("DELETE")
 */
class DeleteBus extends AbstractDeleteCommand
{
    //
}
