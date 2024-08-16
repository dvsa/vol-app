<?php

/**
 * Queue run scoring permitted
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

class QueueRunScoringPermitted extends AbstractQuery
{
    use Identity;
}
