<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Conviction;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class Conviction
 * @Transfer\RouteName("backend/conviction/single")
 */
class Conviction extends AbstractQuery
{
    use FieldTypeTraits\Identity;
}
