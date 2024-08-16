<?php

/**
 * Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Trailer;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Trailer extends AbstractQueryHandler
{
    protected $repoServiceName = 'Trailer';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result($this->getRepo()->fetchUsingId($query));
    }
}
