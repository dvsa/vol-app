<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Window
 *
 * @author Andy Newton
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';
    protected $bundle = ['irhpPermitStock' => ['irhpPermitType' => ['name']]];

    public function handleQuery(QueryInterface $query)
    {
        $irhpPermitWindows = $this->getRepo()->fetchByIrhpPermitStockId($query->getIrhpPermitStock());

        return [
            'result' => $this->resultList(
                $irhpPermitWindows,
                $this->bundle
            )
        ];
    }
}
