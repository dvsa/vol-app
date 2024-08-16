<?php

/**
 * Publication by traffic area and pub type
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Publication by traffic area and pub type
 */
class PublicationLatestByTaAndTypeBundle extends AbstractQueryHandler
{
    protected $repoServiceName = 'Publication';

    public function handleQuery(QueryInterface $query)
    {
        $results = $this->getRepo()->fetchLatestForTrafficAreaAndType($query->getTrafficArea(), $query->getPubType());

        return [
            'Results' => $this->resultList($results, $query->getBundle())
        ];
    }
}
