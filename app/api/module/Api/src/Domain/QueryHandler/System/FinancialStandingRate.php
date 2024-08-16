<?php

/**
 * FinancialStandingRate
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;
use Dvsa\Olcs\Api\Entity\FinancialStandingRate\FinancialStandingRate as Entity;

/**
 * FinancialStandingRate
 */
class FinancialStandingRate extends AbstractQueryHandler
{
    protected $repoServiceName = 'FinancialStandingRate';

    public function handleQuery(QueryInterface $query)
    {
        /** @var Repo $repo */
        $repo = $this->getRepo();

        $fsr = $repo->fetchUsingId($query);

        return $this->result(
            $fsr,
            [
                'goodsOrPsv',
                'licenceType',
            ]
        );
    }
}
