<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\FeePerPermit as IrhpFeePerPermitDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Irhp fee per permit data source config
 */
class IrhpFeePerPermit extends AbstractDataSource
{
    const DATA_KEY = 'feePerPermit';
    protected $dto = IrhpFeePerPermitDto::class;
}
