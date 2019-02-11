<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\MaxStockPermitsByApplication as IrhpMaxStockPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Irhp max permits by stock data source config
 */
class IrhpMaxStockPermits extends AbstractDataSource
{
    const DATA_KEY = 'maxStockPermits';
    protected $dto = IrhpMaxStockPermitsDto::class;
}
