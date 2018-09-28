<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\UnpaidEcmtPermits as UnpaidEcmtPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Unpaod ECMT permits data source config
 */
class UnpaidEcmtPermits extends AbstractDataSource
{
    const DATA_KEY = 'validPermits';
    protected $dto = UnpaidEcmtPermitsDto::class;
    protected $paramsMap = [
        'id' => 'id',
        'page' => 'page',
        'limit' => 'limit',
        'status' => 'status',
    ];
    protected $defaultParamData = [
        'page' => 1,
        'limit' => 10,
        'status' => 'ecmt_permit_awaiting',
    ];
}
