<?php

/**
 * Query Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler;

use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Query Handler Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface QueryHandlerInterface
{
    public function handleQuery(QueryInterface $query);

    /**
     * @return bool
     * @throws DisabledHandlerException
     */
    public function checkEnabled(): bool;
}
