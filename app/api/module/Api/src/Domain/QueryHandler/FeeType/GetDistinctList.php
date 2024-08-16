<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\FeeType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\FeeType;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Get a list of distinct FeeTypes visible within internal fee admin
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class GetDistinctList extends AbstractQueryHandler
{
    protected $repoServiceName = 'FeeType';

    public function handleQuery(QueryInterface $query)
    {
        /** @var FeeType $repo */
        $repo = $this->getRepo();

        return [
            'results' => $repo->fetchDistinctFeeTypesVisibleInInternal()
        ];
    }
}
