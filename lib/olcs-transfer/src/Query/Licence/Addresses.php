<?php

/**
 * Licence - Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence/single/addresses")
 */
class Addresses extends AbstractQuery
{
    use Identity;
}
