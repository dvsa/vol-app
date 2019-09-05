<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\UnpaidEcmtPermits as UnpaidEcmtPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;
use Common\RefData;

/**
 * Unpaid ECMT permits data source config
 */
class UnpaidEcmtPermits extends AbstractDataSource
{
    const DATA_KEY = 'unpaidPermits';
    protected $dto = UnpaidEcmtPermitsDto::class;
    protected $paramsMap = [
        'id' => 'id',
        'page' => 'page',
        'limit' => 'limit',
    ];
    protected $defaultParamData = [
        'page' => 1,
        'limit' => 10,
        'status' => RefData::PERMIT_APP_STATUS_AWAITING_FEE,
    ];
}
