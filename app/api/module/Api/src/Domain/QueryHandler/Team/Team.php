<?php

/**
 * Team
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Team;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Team
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Team extends AbstractQueryHandler
{
    protected $repoServiceName = 'Team';

    public function handleQuery(QueryInterface $query)
    {
        $team = $this->getRepo()->fetchUsingId($query);
        return $this->result($team, ['trafficArea', 'teamPrinters' => ['printer', 'user', 'subCategory']]);
    }
}
