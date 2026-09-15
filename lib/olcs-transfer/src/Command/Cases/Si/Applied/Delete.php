<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Si\Applied;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/si-penalty-applied/single")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
    //
}
