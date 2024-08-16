<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a permit type by id
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpPermitType';
}
