<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\Messages;

use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/messages/unread-count-by-organisation-and-user")
 */
final class UnreadCountByOrganisationAndUser extends AbstractQuery
{
    use Organisation;
    use User;
}
