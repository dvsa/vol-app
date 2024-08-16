<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * Get rule
 */
class GetRule extends AbstractQueryHandler
{
    protected $repoServiceName = 'DataRetentionRule';

    /**
     * Return list of data retention rules that are enabled and action is 'Review'
     *
     * @param QueryInterface|GetRule $query Query for data retention rule list
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query)
        );
    }
}
