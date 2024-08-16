<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System\PublicHoliday;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Handler for GET a public holiday
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'PublicHoliday';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query)
        );
    }
}
