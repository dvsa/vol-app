<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\NextIrhpPermitStock as NextIrhpPermitStockDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Open windows data source config
 */
class NextIrhpPermitStock extends AbstractDataSource
{
    const DATA_KEY = 'nextIrhpPermitStock';
    protected $dto = NextIrhpPermitStockDto::class;

    public function __construct()
    {
        $this->extraQueryData['permitType'] = 'permit_ecmt';
    }
}
