<?php

/**
 * Team Printer Exceptions
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TeamPrinter;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Team Printer Exceptions
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamPrinterExceptionsList extends AbstractQueryHandler
{
    protected $repoServiceName = 'TeamPrinter';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'team',
                    'printer',
                    'user' => [
                        'contactDetails' => [
                            'person'
                        ]
                    ],
                    'subCategory' => [
                        'category'
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
