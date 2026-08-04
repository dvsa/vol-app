<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Impounding;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 *
 * @Transfer\RouteName("backend/impounding/single")
 * @Transfer\Method("DELETE")
 */
class DeleteImpounding extends AbstractDeleteCommand
{
    //
}
