<?php

namespace Dvsa\Olcs\Transfer\Query\Publication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Recipient
 * @Transfer\RouteName("backend/publication/recipient/single")
 */
class Recipient extends AbstractQuery
{
    use Identity;
}
