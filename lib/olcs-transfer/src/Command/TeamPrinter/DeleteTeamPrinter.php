<?php

/**
 * Delete Team Printer
 */

namespace Dvsa\Olcs\Transfer\Command\TeamPrinter;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/printer-exception/single")
 * @Transfer\Method("DELETE")
 */
final class DeleteTeamPrinter extends AbstractDeleteCommand
{
    //
}
