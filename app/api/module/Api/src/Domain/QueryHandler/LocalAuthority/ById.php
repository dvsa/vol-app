<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LocalAuthority;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a local authority by id
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LocalAuthority';
}
