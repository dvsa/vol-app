<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as PermitApplicationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Permit application data source config
 */
class PermitApplication extends AbstractDataSource
{
    /** @todo this should be something more specific e.g. permitApplication but it's a BC break for current views */
    public const DATA_KEY = 'application';
    protected $dto = PermitApplicationDto::class;
}
