<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Conviction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/conviction/single")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
    //
}
