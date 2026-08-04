<?php

namespace Dvsa\Olcs\Transfer\Command\System\PublicHoliday;

use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/public-holiday/single")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
}
