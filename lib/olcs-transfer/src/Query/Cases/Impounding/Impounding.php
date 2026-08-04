<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Impounding;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Impounding
 * @Transfer\RouteName("backend/impounding/single")
 */
class Impounding extends AbstractQuery
{
    use Identity;
}
