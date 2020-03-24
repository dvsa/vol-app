<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Fee\IrhpBilateralByCountry;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Bilateral fees by country data source config
 */
class BilateralFeesByCountry extends AbstractDataSource
{
    const DATA_KEY = 'countryFee';
    protected $dto = IrhpBilateralByCountry::class;
    protected $paramsMap = ['country' => 'country'];
}
