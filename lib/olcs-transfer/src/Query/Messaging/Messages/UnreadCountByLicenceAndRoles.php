<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\Messages;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\RolesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/messages/unread-count-by-licence-and-roles")
 */
final class UnreadCountByLicenceAndRoles extends AbstractQuery
{
    use Licence;
    use RolesOptional;
}
