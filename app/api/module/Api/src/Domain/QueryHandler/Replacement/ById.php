<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Replacement;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a replacement by id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'Replacement';
}
