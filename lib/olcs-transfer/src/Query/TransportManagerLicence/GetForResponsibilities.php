<?php

/**
 * Get Transport Manager Licence details for responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\TransportManagerLicence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/tm-responsibilities/transport-manager-licence/single")
 */
class GetForResponsibilities extends AbstractQuery
{
    use Identity;
}
