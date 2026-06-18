<?php

namespace Dvsa\Olcs\Transfer\Query\Correspondence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Correspondence
 * @Transfer\RouteName("backend/correspondence/single")
 */
class Correspondence extends AbstractQuery
{
    use Identity;
}
