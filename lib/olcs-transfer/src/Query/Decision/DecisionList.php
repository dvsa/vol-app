<?php

namespace Dvsa\Olcs\Transfer\Query\Decision;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Class DecisionList
 * @Transfer\RouteName("backend/decision")
 */
class DecisionList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use FieldType\GoodsOrPsvOptional;
    use FieldType\IsNiOptional;
}
