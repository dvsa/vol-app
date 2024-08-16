<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * PI
 */
final class LegacyOffence extends AbstractQueryHandler
{
    protected $repoServiceName = 'LegacyOffence';

    /**
     * Handle query
     *
     * @param QueryInterface $query query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchCaseLegacyOffenceUsingId($query),
            [
                'case',
                'lastModifiedBy',
                'createdBy'
            ]
        );
    }
}
