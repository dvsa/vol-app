<?php

namespace Dvsa\Olcs\Transfer\Query\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class SurrenderBusReg
 *
 * @package Dvsa\Olcs\Transfer\Query\Surrender
 * @Transfer\RouteName("backend/licence/single/surrender/open-bus-reg")
 * @Transfer\Method("GET")
 */
class OpenBusReg extends AbstractQuery
{
    use Identity;
}
