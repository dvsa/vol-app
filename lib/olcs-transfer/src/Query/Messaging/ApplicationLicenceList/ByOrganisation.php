<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Transfer\FieldType\Traits\OrganisationOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/application-licence-list/by-organisation")
 */
final class ByOrganisation extends AbstractQuery
{
    use OrganisationOptional;
}
