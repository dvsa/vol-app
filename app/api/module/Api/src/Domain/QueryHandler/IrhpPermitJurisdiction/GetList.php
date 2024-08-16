<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitJurisdiction;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Jurisdiction
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitJurisdictionQuota';

    private $bundle = ['trafficArea'];

    /**
     * Handle Query
     *
     * @param QueryInterface $query
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        $irhpPermitJurisdiction = $this->getRepo()->fetchByIrhpPermitStockId($query->getIrhpPermitStock());

        return [
            'result' => $this->resultList(
                $irhpPermitJurisdiction,
                $this->bundle
            )
        ];
    }
}
