<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/application-licence-list/by-application-to-organisation")
 */
final class ByApplicationToOrganisation extends AbstractQuery
{
    use Application;
}
