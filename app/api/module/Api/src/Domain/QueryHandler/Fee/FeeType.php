<?php

/**
 * FeeType
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;

/**
 * FeeType
 */
class FeeType extends AbstractQueryHandler
{
    protected $repoServiceName = 'FeeType';

    public function handleQuery(QueryInterface $query)
    {
        /** @var FeeTypeRepository $repo */
        $repo = $this->getRepo();

        $feeType = $repo->fetchUsingId($query);

        return $this->result(
            $feeType,
            [],
            [
                'showQuantity' => $feeType->isShowQuantity()
            ]
        );
    }
}
