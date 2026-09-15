<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/application-licence-list/by-licence-to-organisation")
 */
final class ByLicenceToOrganisation extends AbstractQuery
{
    use Licence;
}
