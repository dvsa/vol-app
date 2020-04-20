<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\FeeBreakdown as FeeBreakdownDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * IRHP fee breakdown data source config
 */
class IrhpFeeBreakdown extends AbstractDataSource
{
    const DATA_KEY = 'feeBreakdown';
    protected $dto = FeeBreakdownDto::class;
}
