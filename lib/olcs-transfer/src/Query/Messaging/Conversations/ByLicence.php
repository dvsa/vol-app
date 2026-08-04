<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\Messaging\Conversations;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\Messaging\StatusesOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/messaging/conversations/by-licence")
 */
final class ByLicence extends AbstractQuery implements PagedQueryInterface
{
    use PagedTrait;
    use Licence;
    use StatusesOptional;
}
