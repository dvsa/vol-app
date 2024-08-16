<?php

/**
 * CpidOrganisation.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Organisation;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Class CpidOrganisation
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CpidOrganisation extends AbstractQueryHandler
{
    protected $repoServiceName = 'Organisation';

    public function handleQuery(QueryInterface $query)
    {
        $result = $this->getRepo()->fetchByStatusPaginated($query);

        return [
            'result' => $this->resultList($result['result']),
            'count' => $result['count']
        ];
    }
}
