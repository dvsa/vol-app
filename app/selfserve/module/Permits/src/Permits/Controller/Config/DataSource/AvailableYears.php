<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears as AvailableYearsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Available years data source config
 */
class AvailableYears extends AbstractDataSource
{
    const DATA_KEY = 'years';
    protected $dto = AvailableYearsDto::class;
    protected $paramsMap = [
        'type' => 'type'
    ];
}
