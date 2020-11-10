<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Wanted unpaid IRHP permits data source config
 */
class WantedUnpaidIrhpPermits extends AbstractDataSource
{
    const DATA_KEY = 'wantedUnpaidPermits';
    protected $dto = GetListByIrhpApplicationUnpaged::class;
    protected $paramsMap = [
        'id' => 'irhpApplication',
    ];
    protected $defaultParamData = [
        'sort' => 'id',
        'order' => 'ASC',
        'wantedOnly' => true
    ];
}
