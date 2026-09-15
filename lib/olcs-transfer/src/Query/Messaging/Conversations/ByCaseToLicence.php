<?php

namespace Dvsa\Olcs\Transfer\Query\Messaging\Conversations;

use Dvsa\Olcs\Transfer\FieldType\Traits\Cases;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\StatusesOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/conversations/by-case-to-licence")
 */
final class ByCaseToLicence extends AbstractQuery implements PagedQueryInterface
{
    use PagedTrait;
    use Cases;
    use StatusesOptional;
}
