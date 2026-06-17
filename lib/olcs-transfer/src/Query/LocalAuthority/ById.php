<?php

/**
 * Get a single local authority by id
 */

namespace Dvsa\Olcs\Transfer\Query\LocalAuthority;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/local-authority/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}
