<?php

namespace Dvsa\Olcs\Transfer\Query\DataRetention;

use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * @Transfer\RouteName("backend/data-retention/rule-admin")
 */
final class RuleAdmin extends AbstractQuery implements
    PagedQueryInterface,
    OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
}
