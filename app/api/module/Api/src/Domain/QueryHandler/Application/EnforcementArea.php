<?php

/**
 * Enforcement Area
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Enforcement Area
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EnforcementArea extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $application,
            [
                'licence' => ['enforcementArea']
            ]
        );
    }
}
