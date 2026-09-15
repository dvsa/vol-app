<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\PresidingTc;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class StatementList
 * @Transfer\RouteName("backend/presiding-tc")
 */
class GetList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
}
