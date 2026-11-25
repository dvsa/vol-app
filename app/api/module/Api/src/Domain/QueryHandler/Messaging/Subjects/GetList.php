<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Subjects;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\MessagingSubject as Repo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

final class GetList extends AbstractQueryHandler
{
    protected $extraRepos = [Repo::class];

    #[\Override]
    public function handleQuery(QueryInterface $query): array
    {
        $repo = $this->getRepo(Repo::class);

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                ['category', 'subCategory']
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
