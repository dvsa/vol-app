<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\GracePeriod;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * GracePeriod
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriod extends AbstractQueryHandler
{
    protected $repoServiceName = 'GracePeriod';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriod $query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query)
        );
    }
}
