<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Complaint;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Complaint
 */
final class Complaint extends AbstractQueryHandler
{
    protected $repoServiceName = 'Complaint';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'complainantContactDetails' => [
                    'person'
                ]
            ]
        );
    }
}
