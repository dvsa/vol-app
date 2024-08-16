<?php

/**
 * Application - Previous Convictions
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Application - Previous Convictions
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PreviousConvictions extends AbstractQueryHandler
{
    protected $repoServiceName = 'Application';

    public function handleQuery(QueryInterface $query)
    {
        $application = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $application,
            [
                'previousConvictions'
            ]
        );
    }
}
