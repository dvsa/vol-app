<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Trailers
 * @Transfer\RouteName("backend/licence/single/trailers")
 */
class Trailers extends AbstractQuery
{
    use Identity;
}
