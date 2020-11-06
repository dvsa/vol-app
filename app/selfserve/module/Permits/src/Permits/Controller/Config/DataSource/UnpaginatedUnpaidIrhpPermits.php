<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication as UnpaidPermitsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Unpaginated unpaid IRHP permits data source config
 */
class UnpaginatedUnpaidIrhpPermits extends AbstractDataSource
{
    const DATA_KEY = 'unpaginatedUnpaidPermits';
    protected $dto = UnpaidPermitsDto::class;
    protected $paramsMap = [
        'id' => 'irhpApplication',
    ];
    protected $defaultParamData = [
        'page' => 1,
        'limit' => 100,
        'sort' => 'id',
        'order' => 'ASC',
    ];
}
