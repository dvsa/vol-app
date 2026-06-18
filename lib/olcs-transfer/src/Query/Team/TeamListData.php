<?php

/**
 * This query is used on internal only to retrieve a list of teams.
 * For internal users with limited permissions, the backend will proxy
 * to Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea
 */

namespace Dvsa\Olcs\Transfer\Query\Team;

use Dvsa\Olcs\Transfer\Query\AbstractListData;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/team/list-data")
 */
final class TeamListData extends AbstractListData
{
}
