<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a permit window by id
 *
 * @author Andy Newton
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpPermitWindow';
    protected $bundle = ['irhpPermitStock'];
}
