<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Si;

use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/case-si/single")
 * @Transfer\Method("DELETE")
 */
class DeleteSi extends AbstractDeleteCommand
{
    //
}
