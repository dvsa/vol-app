<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\EndDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\IdentityString;
use Dvsa\Olcs\Transfer\FieldType\Traits\StartDate;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/queue-report")
 * @Transfer\Method("POST")
 */
final class QueueReport extends AbstractCommand
{
    use IdentityString;
    use StartDate;
    use EndDate;
}
