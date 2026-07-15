<?php

/**
 * Queue accept scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Permits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/permits/queue-accept-scoring")
 * @Transfer\Method("POST")
 */
final class QueueAcceptScoring extends AbstractCommand
{
    use Identity;
}
