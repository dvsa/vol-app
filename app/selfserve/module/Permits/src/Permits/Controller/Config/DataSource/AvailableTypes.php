<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableTypes as AvailableTypesDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Available types data source config
 */
class AvailableTypes extends AbstractDataSource
{
    const DATA_KEY = 'types';
    protected $dto = AvailableTypesDto::class;
}
