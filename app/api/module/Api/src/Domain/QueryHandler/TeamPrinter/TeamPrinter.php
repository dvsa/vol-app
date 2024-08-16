<?php

/**
 * Team Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TeamPrinter;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Team Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamPrinter extends AbstractQueryHandler
{
    protected $repoServiceName = 'TeamPrinter';

    public function handleQuery(QueryInterface $query)
    {
        $team = $this->getRepo()->fetchUsingId($query);
        return $this->result(
            $team,
            [
                'user',
                'team',
                'printer',
                'subCategory' => ['category']
            ]
        );
    }
}
