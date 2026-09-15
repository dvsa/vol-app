<?php

namespace Dvsa\Olcs\Transfer\Command\InspectionRequest;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Delete Inspection Request
 *
 * @Transfer\RouteName("backend/inspection-request/single")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
    //
}
