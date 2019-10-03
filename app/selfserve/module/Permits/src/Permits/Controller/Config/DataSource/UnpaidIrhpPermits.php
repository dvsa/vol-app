<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as UnpaidPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Unpaid IRHP permits data source config
 */
class UnpaidIrhpPermits extends AbstractDataSource
{
    const DATA_KEY = 'unpaidPermits';
    protected $dto = UnpaidPermitsDto::class;
    protected $paramsMap = [
        'id' => 'irhpApplication',
        'page' => 'page',
        'limit' => 'limit',
    ];
    protected $defaultParamData = [
        'page' => 1,
        'limit' => 10,
        'sort' => 'id',
        'order' => 'ASC',
    ];
}
