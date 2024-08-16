<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Gets a list of licences ready to go Continuation Not Sought
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ContinuationNotSoughtList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    /**
     * Handle query
     *
     * @param \Dvsa\Olcs\Api\Domain\Query\Licence\ContinuationNotSoughtList $query query
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        // this query is recursion save, no need to serialise, already serialised
        $results = $this->getRepo()->fetchForContinuationNotSought($query->getDate());
        return [
            'result' => $results,
            'count' => count($results),
        ];
    }
}
