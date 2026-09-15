<?php

namespace Dvsa\Olcs\Transfer\Query\DataRetention;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/data-retention/rule-list/single")
 */
final class GetRule extends AbstractQuery
{
    use Identity;
}
