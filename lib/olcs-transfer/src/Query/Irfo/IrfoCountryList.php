<?php

namespace Dvsa\Olcs\Transfer\Query\Irfo;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * Class IrfoCountryList
 * @Transfer\RouteName("backend/irfo/country-list")
 */
class IrfoCountryList extends AbstractQuery implements CacheableLongTermQueryInterface
{
    //
}
