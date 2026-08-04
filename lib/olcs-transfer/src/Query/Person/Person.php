<?php

namespace Dvsa\Olcs\Transfer\Query\Person;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Opposition
 * @Transfer\RouteName("backend/person/single")
 */
class Person extends AbstractQuery
{
    use Identity;
}
