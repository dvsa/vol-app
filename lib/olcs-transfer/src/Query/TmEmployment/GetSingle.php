<?php

namespace Dvsa\Olcs\Transfer\Query\TmEmployment;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class GetSingle
 *
 * @Transfer\RouteName("backend/tm-employment/single")
 */
class GetSingle extends AbstractQuery
{
    use Identity;
}
