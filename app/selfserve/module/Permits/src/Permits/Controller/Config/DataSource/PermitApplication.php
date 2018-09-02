<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\ById as PermitApplicationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Check answers
 */
class PermitApplication extends AbstractDataSource
{
    /** @todo this should be something more specific e.g. permitApplication but it's a BC break for current views */
    const DATA_KEY = 'application';
    protected $dto = PermitApplicationDto::class;
}
