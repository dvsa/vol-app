<?php

/**
 * IrhpPermit by Permit Number
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange\ByPermitNumber as ByPermitNumberQry;

class ByPermitNumber extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpPermitRange';

    /**
     * @param QueryInterface|ByPermitNumberQry $query query
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        return $this->getRepo()->fetchByPermitNumberAndStock($query->getPermitNumber(), $query->getPermitStock());
    }
}
