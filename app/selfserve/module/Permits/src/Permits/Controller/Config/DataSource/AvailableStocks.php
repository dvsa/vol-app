<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks as AvailableStocksDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Available stocks data source config
 */
class AvailableStocks extends AbstractDataSource
{
    const DATA_KEY = 'stocks';
    protected $dto = AvailableStocksDto::class;
    protected $paramsMap = [
        'type' => 'irhpPermitType',
        'year' => 'year',
    ];
}
