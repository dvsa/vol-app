<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible as BilateralCountryAccessibleQry;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Bilateral country accessible data source config
 */
class BilateralCountryAccessible extends AbstractDataSource
{
    const DATA_KEY = 'bilateralCountryAccessible';
    protected $dto = BilateralCountryAccessibleQry::class;
    protected $paramsMap = ['id' => 'id', 'country' => 'country'];
}
