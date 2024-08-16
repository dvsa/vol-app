<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Document By Doc Store Id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ByDocumentStoreId extends AbstractQueryHandler
{
    protected $repoServiceName = 'Document';

    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchByDocumentStoreId($query->getDocumentStoreId());
    }
}
