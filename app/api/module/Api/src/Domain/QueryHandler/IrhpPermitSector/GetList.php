<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitSector;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Sector
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitSectorQuota';

    private $bundledRepos = ['irhpPermitStock', 'sector'];

    /**
     * Handle Query
     *
     * @param QueryInterface $query
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpPermitSectors = $this->getRepo()->fetchByIrhpPermitStockId($query->getIrhpPermitStock());

        return [
            'result' => $this->resultList(
                $irhpPermitSectors,
                $this->bundledRepos
            )
        ];
    }
}
