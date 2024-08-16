<?php

/**
 * Irfo Gv Permit
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Irfo;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Irfo Gv Permit
 */
class IrfoGvPermit extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrfoGvPermit';

    protected $extraRepos = ['Fee'];

    public function handleQuery(QueryInterface $query)
    {
        $irfoGvPermit = $this->getRepo()->fetchUsingId($query);

        return $this->result(
            $irfoGvPermit,
            [
                'irfoGvPermitType'
            ],
            [
                'isApprovable' => $irfoGvPermit->isApprovable(
                    $this->getRepo('Fee')->fetchFeesByIrfoGvPermitId($query->getId())
                ),
                'isGeneratable' => $irfoGvPermit->isGeneratable()
            ]
        );
    }
}
