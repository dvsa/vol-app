<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Correspondence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Correspondence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Correspondence extends AbstractQueryHandler
{
    protected $repoServiceName = 'Correspondence';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'document',
                'licence',
            ]
        );
    }
}
