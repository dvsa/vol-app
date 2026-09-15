<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Messaging\ApplicationLicenceList;

use Dvsa\Olcs\Transfer\FieldType\Traits\Cases;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/application-licence-list/by-case-to-organisation")
 */
final class ByCaseToOrganisation extends AbstractQuery
{
    use Cases;
}
