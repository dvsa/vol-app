<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByStockAndLicence as MaxPermittedReachedByStockAndLicenceDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Max permitted reached for stock data source config
 */
class MaxPermittedReachedForStock extends AbstractDataSource
{
    const DATA_KEY = 'maxPermittedReached';
    protected $dto = MaxPermittedReachedByStockAndLicenceDto::class;
    protected $paramsMap = [
        'stock' => 'irhpPermitStock',
        'licence' => 'licence'
    ];
}
