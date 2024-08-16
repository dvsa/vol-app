<?php

/**
 * Abstract Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Audit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Abstract Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractReadAudit extends AbstractQueryHandler
{
    public function handleQuery(QueryInterface $query)
    {
        $this->getRepo()->disableSoftDeleteable();

        return [
            'results' => $this->resultList(
                $this->getRepo()->fetchList($query, Query::HYDRATE_OBJECT),
                [
                    'user' => [
                        'contactDetails' => [
                            'person'
                        ]
                    ]
                ]
            ),
            'count' => $this->getRepo()->fetchCount($query),
        ];
    }
}
