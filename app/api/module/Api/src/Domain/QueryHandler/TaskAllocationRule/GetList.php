<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\TaskAllocationRule\GetList as Qry;

class GetList extends AbstractQueryHandler
{
    protected $extraRepos = [
        Repository\TaskAllocationRule::class,
    ];

    /**
     * @param Qry $query
     * @return array
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query): array
    {
        $repo = $this->getRepo(Repository\TaskAllocationRule::class);

        $repo->disableSoftDeleteable();

        $result = $repo->fetchList($query, \Doctrine\ORM\Query::HYDRATE_OBJECT);

        //  remove deleted users
        /** @var  Entity\Task\TaskAllocationRule $rule */
        foreach ($result as $rule) {
            $this->cleanDeletedUser($rule);

            /** @var Entity\Task\TaskAlphaSplit $split */
            foreach ($rule->getTaskAlphaSplits() as $split) {
                $this->cleanDeletedUser($split);
            }
        }

        return [
            'result' => $this->resultList(
                $result,
                [
                    'category',
                    'subCategory',
                    'team',
                    'user' => ['contactDetails' => ['person']],
                    'trafficArea',
                    'taskAlphaSplits' => ['user' => ['contactDetails' => ['person']]],
                ]
            ),
            'count' => $repo->fetchCount($query),
            'count-unfiltered' => $repo->hasRows($query),
        ];
    }

    /**
     * Remove user entity from entity if user has DeletedDate not null (soft-deleted)
     *
     * @param Entity\Task\TaskAllocationRule|Entity\Task\TaskAlphaSplit $entity
     */
    private function cleanDeletedUser($entity): void
    {
        $user = $entity->getUser();

        if ($user !== null && $user->getDeletedDate() !== null) {
            $entity->setUser(null);
        }
    }
}
