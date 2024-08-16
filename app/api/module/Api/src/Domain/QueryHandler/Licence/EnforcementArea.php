<?php

/**
 * Enforcement Area
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Enforcement Area
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EnforcementArea extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        $licence = $this->getRepo()->fetchUsingId($query);
        return $this->result(
            $licence,
            ['enforcementArea']
        );
    }
}
