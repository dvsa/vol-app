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
 * Class RegistrationHistoryList
 * @Transfer\RouteName("backend/bus/registration-history-list")
 */
class RegistrationHistoryList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use FieldTypeTraits\Identity;
}
