<?php

/**
 * Trailers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Trailers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Trailers extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    protected $extraRepos = ['Trailer'];

    public function handleQuery(QueryInterface $query)
    {
        $licence = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $licence,
            [
                'organisation'
            ],
            [
                'trailers' => [
                    'results' => $this->getRepo('Trailer')->fetchList($query),
                    'count' => $this->getRepo('Trailer')->fetchCount($query)
                ]
            ]
        );
    }
}
