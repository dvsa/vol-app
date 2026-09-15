<?php

/**
 * Undo disqualification
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Tm;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/transport-manager/single/undo-disqualification")
 * @Transfer\Method("PUT")
 */
final class UndoDisqualification extends AbstractCommand
{
    use Identity;
}
