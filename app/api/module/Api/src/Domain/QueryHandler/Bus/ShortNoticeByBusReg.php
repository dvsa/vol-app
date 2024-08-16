<?php

/**
 * Short notice by bus reg
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Short notice by bus reg
 */
class ShortNoticeByBusReg extends AbstractQueryHandler
{
    protected $repoServiceName = 'BusShortNotice';

    public function handleQuery(QueryInterface $query)
    {
        $shortNotice = $this->getRepo()->fetchByBusReg($query);

        return [
            'result' => $this->resultList(
                $shortNotice,
                [
                    'busReg' => [
                        'status'
                    ]
                ]
            )
        ];
    }
}
