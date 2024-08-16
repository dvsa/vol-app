<?php

/**
 * Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Document extends AbstractQueryHandler
{
    protected $repoServiceName = 'Document';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result($this->getRepo()->fetchUsingId($query));
    }
}
