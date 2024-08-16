<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a permit stock by id
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpPermitStock';
    protected $bundle = [
        'irhpPermitType' => ['name'],
        'country',
        'applicationPathGroup',
        'irhpPermitRanges' => ['emissionsCategory']
    ];
}
