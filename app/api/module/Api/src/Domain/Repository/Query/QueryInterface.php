<?php

/**
 * Query Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query;

/**
 * Query Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface QueryInterface
{
    /**
     * Execute query
     *
     * @return mixed
     */
    public function execute(array $params = [], array $paramTypes = []);
}
