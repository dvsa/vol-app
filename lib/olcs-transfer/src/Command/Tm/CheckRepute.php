<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Tm;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/transport-manager/check-repute")
 * @Transfer\Method("POST")
 */
final class CheckRepute extends AbstractCommand
{
    use Identity;
}
