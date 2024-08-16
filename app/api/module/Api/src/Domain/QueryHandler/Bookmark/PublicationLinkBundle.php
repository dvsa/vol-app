<?php

/**
 * Publication Link Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Publication Link Bundle Bookmark
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PublicationLinkBundle extends AbstractQueryHandler
{
    protected $repoServiceName = 'PublicationLink';

    public function handleQuery(QueryInterface $query)
    {
        $results = $this->getRepo()->fetchByBusRegId($query->getBusReg());

        return [
            'Results' => $this->resultList($results, $query->getBundle())
        ];
    }
}
