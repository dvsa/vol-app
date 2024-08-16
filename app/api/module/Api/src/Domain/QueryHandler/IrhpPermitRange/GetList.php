<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IRHP Range
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class GetList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitRange';

    private $bundle = [
        'countrys',
        'irhpPermitStock' => [
            'irhpPermitType' => ['name'],
            'permitCategory',
        ],
        'emissionsCategory',
        'journey'
    ];

    public function handleQuery(QueryInterface $query)
    {
        $irhpPermitRanges = $this->getRepo()->fetchByIrhpPermitStockId($query->getIrhpPermitStock());
        return [
            'result' => $this->resultList(
                $irhpPermitRanges,
                $this->bundle
            )
        ];
    }
}
