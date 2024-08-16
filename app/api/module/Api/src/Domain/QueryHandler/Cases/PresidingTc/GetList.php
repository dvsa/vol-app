<?php

/**
 * Get Presiding TC list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\PresidingTc;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\PresidingTc as PresidingTcRepo;

/**
 * Get Presiding TC list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'PresidingTc';

    public function handleQuery(QueryInterface $query)
    {
        /** @var PresidingTcRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['user' => [
                    'contactDetails' => [
                        'person'
                    ]
                ]]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
