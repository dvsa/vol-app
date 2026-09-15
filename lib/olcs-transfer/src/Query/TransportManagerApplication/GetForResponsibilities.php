<?php

/**
 * Get Transport Manager Application details for responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\TransportManagerApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/tm-responsibilities/transport-manager-application/single")
 */
class GetForResponsibilities extends AbstractQuery
{
    use Identity;
}
