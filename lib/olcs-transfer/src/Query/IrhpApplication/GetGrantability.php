<?php

/** Get Grantability of Irhp Application */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/irhp-application/get-grantability")
 */
final class GetGrantability extends AbstractQuery
{
    use Identity;
}
