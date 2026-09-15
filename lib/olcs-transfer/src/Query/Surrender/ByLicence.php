<?php

namespace Dvsa\Olcs\Transfer\Query\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class ById
 *
 * @package Dvsa\Olcs\Transfer\Query\Surrender
 * @Transfer\RouteName("backend/licence/single/surrender")
 * @Transfer\Method("GET")
 */
class ByLicence extends AbstractQuery
{
    use Identity;
}
