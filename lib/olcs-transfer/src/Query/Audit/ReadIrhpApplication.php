<?php

namespace Dvsa\Olcs\Transfer\Query\Audit;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/audit/read/irhp-application")
 */
class ReadIrhpApplication extends AbstractQuery implements PagedQueryInterface
{
    use Identity;
    use PagedTrait;
}
