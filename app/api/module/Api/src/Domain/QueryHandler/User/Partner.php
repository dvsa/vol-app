<?php

/**
 * Partner
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Partner
 */
class Partner extends AbstractQueryHandler
{
    protected $repoServiceName = 'Partner';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'address' => ['countryCode']
            ]
        );
    }
}
