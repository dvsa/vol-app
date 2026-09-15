<?php

namespace Dvsa\Olcs\Transfer\Command\TmCaseDecision;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/tm-case-decision/single")
 * @Transfer\Method("DELETE")
 */
class Delete extends AbstractDeleteCommand
{
    //
}
