<?php

/**
 * Unmerge Transport Manager
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Tm;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/transport-manager/single/unmerge")
 * @Transfer\Method("PUT")
 */
final class Unmerge extends AbstractCommand
{
    use Identity;
}
