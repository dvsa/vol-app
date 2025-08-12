<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get MasterTemplate by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'MasterTemplate';
}
