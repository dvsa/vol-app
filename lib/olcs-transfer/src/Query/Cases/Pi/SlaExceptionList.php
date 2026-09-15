<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Cases\Pi;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * Get a list of SLA Exceptions
 *
 * @Transfer\RouteName("backend/pi/sla-exceptions")
 */
class SlaExceptionList extends AbstractQuery implements OrderedQueryInterface, CacheableLongTermQueryInterface
{
    use OrderedTrait;
}
