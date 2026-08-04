<?php

namespace Dvsa\Olcs\Transfer\Query\TmQualification;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/tm-qualification/single")
 */
class TmQualification extends AbstractQuery
{
    use Identity;
}
