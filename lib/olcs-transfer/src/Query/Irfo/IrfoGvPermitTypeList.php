<?php

namespace Dvsa\Olcs\Transfer\Query\Irfo;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * Class IrfoPsvAuthList
 * @Transfer\RouteName("backend/irfo/gv-permit/type-list")
 */
class IrfoGvPermitTypeList extends AbstractQuery implements CacheableLongTermQueryInterface
{
    //
}
