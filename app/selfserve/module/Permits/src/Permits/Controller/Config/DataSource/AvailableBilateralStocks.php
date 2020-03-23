<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableBilateral as AvailableBilateralDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Available bilateral stocks data source config
 */
class AvailableBilateralStocks extends AbstractDataSource
{
    const DATA_KEY = 'stocks';
    protected $dto = AvailableBilateralDto::class;
    protected $paramsMap = [
        'country' => 'country',
    ];
}
