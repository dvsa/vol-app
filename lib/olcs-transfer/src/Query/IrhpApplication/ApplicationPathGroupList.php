<?php

/**
 * Get a list of Application Path Groups
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;

/**
 * @Transfer\RouteName("backend/irhp-application/application-path-list")
 */
final class ApplicationPathGroupList extends AbstractQuery implements CacheableShortTermQueryInterface
{
}
