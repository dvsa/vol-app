<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\ById as IrhpApplicationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Irhp application data source config
 */
class IrhpApplication extends AbstractDataSource
{
    const DATA_KEY = 'irhpApplication';
    protected $dto = IrhpApplicationDto::class;
}
