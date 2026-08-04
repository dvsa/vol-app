<?php

/**
 * This query is used on internal only to retrieve a list of teams.
 * For internal users with limited permissions, the backend will proxy
 * to Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea
 */

namespace Dvsa\Olcs\Transfer\Query\Team;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * @Transfer\RouteName("backend/team")
 */
final class TeamList extends AbstractQuery implements
    PagedQueryInterface,
    OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
}
