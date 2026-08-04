<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Statement;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Statement
 * @Transfer\RouteName("backend/statement/single")
 */
class Statement extends AbstractQuery
{
    use Identity;
}
