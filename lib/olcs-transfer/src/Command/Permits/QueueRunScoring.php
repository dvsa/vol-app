<?php

/**
 * Queue run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Permits;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\DeviationOptional;

/**
 * @Transfer\RouteName("backend/permits/queue-run-scoring")
 * @Transfer\Method("POST")
 */
final class QueueRunScoring extends AbstractCommand
{
    use Identity;

    use DeviationOptional;
}
