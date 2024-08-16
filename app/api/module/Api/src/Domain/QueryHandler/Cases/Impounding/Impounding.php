<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Impounding;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Impounding
 */
final class Impounding extends AbstractQueryHandler
{
    protected $repoServiceName = 'Impounding';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'presidingTc',
                'impoundingLegislationTypes',
                'venue'
            ]
        );
    }
}
