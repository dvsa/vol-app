<?php

namespace Dvsa\Olcs\Transfer\Query\Trailer;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Trailer
 * @Transfer\RouteName("backend/trailers/single")
 */
class Trailer extends AbstractQuery
{
    use Identity;
}
