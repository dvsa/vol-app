<?php

/**
 * Get a list of IRHP Types
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;

/**
 * @Transfer\RouteName("backend/irhp-permit-type")
 */
final class GetList extends AbstractQuery implements CacheableShortTermQueryInterface
{
}
