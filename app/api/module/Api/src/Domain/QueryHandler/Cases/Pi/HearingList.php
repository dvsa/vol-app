<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as PiHearingRepo;

/**
 * HearingList
 */
final class HearingList extends AbstractQueryHandler
{
    protected $repoServiceName = 'PiHearing';

    public function handleQuery(QueryInterface $query)
    {
        /** @var PiHearingRepo $repo */
        $repo = $this->getRepo();
        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'pi',
                    'venue'
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
