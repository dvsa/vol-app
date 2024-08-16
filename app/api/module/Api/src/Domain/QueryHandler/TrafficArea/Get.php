<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\TrafficArea;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Traffic Area
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'TrafficArea';

    public function handleQuery(QueryInterface $query)
    {
        $trafficArea = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $trafficArea,
            ['trafficAreaEnforcementAreas' => ['enforcementArea']]
        );
    }
}
