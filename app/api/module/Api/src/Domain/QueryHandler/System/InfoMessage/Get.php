<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\System\InfoMessage;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Handler for GET a System info message
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class Get extends AbstractQueryHandler
{
    protected $repoServiceName = 'SystemInfoMessage';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query)
        );
    }
}
