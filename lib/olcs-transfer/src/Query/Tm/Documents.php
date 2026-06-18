<?php

namespace Dvsa\Olcs\Transfer\Query\Tm;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Class Documents
 *
 * @Transfer\RouteName("backend/transport-manager/single/documents")
 */
class Documents extends AbstractQuery
{
    use Identity;
}
