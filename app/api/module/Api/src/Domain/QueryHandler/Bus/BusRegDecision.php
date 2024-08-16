<?php

/**
 * Bus Reg Decision
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as BusRegDecisionQry;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;

/**
 * Bus Reg Decision
 */
class BusRegDecision extends AbstractQueryHandler
{
    protected $repoServiceName = 'Bus';

    /**
     * Handle query
     *
     * @param QueryInterface|BusRegDecisionQry $query query
     *
     * @return Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var BusRegEntity $busReg */
        $busReg = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $busReg,
            [],
            [
                'decision' => $busReg->getDecision(),
                'isGrantable' => $busReg->isGrantable()
            ]
        );
    }
}
