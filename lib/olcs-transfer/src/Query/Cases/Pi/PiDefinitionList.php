<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Pi;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Class ReasonList
 * @Transfer\RouteName("backend/pi/definition")
 */
class PiDefinitionList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use FieldType\GoodsOrPsvOptional;
    use FieldType\IsNiOptional;
}
