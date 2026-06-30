<?php

/**
 * Safety
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;

/**
 * @Transfer\RouteName("backend/licence/single/safety")
 */
class Safety extends AbstractQuery implements PagedQueryInterface
{
    use Identity;
    use PagedTrait;
}
