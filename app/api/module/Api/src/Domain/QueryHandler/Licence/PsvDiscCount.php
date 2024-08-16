<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Psv Discs Count
 */
class PsvDiscCount extends AbstractQueryHandler
{
    protected $repoServiceName = 'PsvDisc';

    /**
     * Handler
     *
     * @param \Dvsa\Olcs\Transfer\Query\Licence\PsvDiscCount $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\PsvDisc $psvDiscRepo */
        $psvDiscRepo = $this->getRepo();
        return $psvDiscRepo->countForLicence($query->getId());
    }
}
