<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Rule Admin
 */
class RuleAdmin extends AbstractQueryHandler
{
    protected $repoServiceName = 'DataRetentionRule';

    /**
     * Return list of data retention rules that are not deleted
     *
     * @param \Dvsa\Olcs\Transfer\Query\DataRetention\RuleAdmin $query Query for data retention rule admin
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var DataRetentionRule $repo */
        $repo = $this->getRepo();

        $notDeletedRules = $repo->fetchAllRules($query);

        return [
            'result' => $this->resultList(
                $notDeletedRules['results']
            ),
            'count' => $notDeletedRules['count']
        ];
    }
}
