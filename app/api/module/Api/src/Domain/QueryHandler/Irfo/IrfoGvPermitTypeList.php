<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoGvPermitType list
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class IrfoGvPermitTypeList extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoGvPermitType';

    public function handleQuery(QueryInterface $query)
    {
        $repo = $this->getRepo();

        $records = $repo->fetchActiveRecords();

        return [
            'result' => $this->resultList($records),
            'count' => count($records),
        ];
    }
}
