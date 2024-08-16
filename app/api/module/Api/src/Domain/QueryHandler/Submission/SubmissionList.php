<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Submission;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Submission as SubmissionRepo;

/**
 * SubmissionList QueryHandler
 */
final class SubmissionList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Submission';

    public function handleQuery(QueryInterface $query)
    {
        /** @var SubmissionRepo $repo */
        $repo = $this->getRepo();

        return [
            'result' => $this->resultList(
                $repo->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'createdBy' => [
                        'contactDetails' => [
                            'person'
                        ]
                    ],
                    'recipientUser' => [
                        'contactDetails' => [
                            'person'
                        ]
                    ]
                ]
            ),
            'count' => $repo->fetchCount($query)
        ];
    }
}
