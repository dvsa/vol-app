<?php

namespace Dvsa\Olcs\Transfer\Query\Bus;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class PaginatedRegistrationHistoryList
 * @Transfer\RouteName("backend/bus/paginated-registration-history-list")
 */
class PaginatedRegistrationHistoryList extends AbstractQuery implements OrderedQueryInterface, PagedQueryInterface
{
    use OrderedTrait;
    use PagedTrait;
    use FieldTypeTraits\Identity;
}
