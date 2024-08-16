<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve a permit range by id
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'IrhpPermitRange';
    protected $bundle = ['countrys', 'irhpPermitStock', 'emissionsCategory', 'journey'];
}
