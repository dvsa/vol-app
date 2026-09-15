<?php

namespace Dvsa\Olcs\Transfer\Query\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @package Dvsa\Olcs\Transfer\Query\Surrender
 * @Transfer\RouteName("backend/licence/single/surrender/previous-licence-status")
 * @Transfer\Method("GET")
 */
class PreviousLicenceStatus extends AbstractQuery
{
    use Identity;
}
