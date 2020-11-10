<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Unpaginated unpaid IRHP permits data source config
 */
class UnpaginatedUnpaidIrhpPermits extends AbstractDataSource
{
    const DATA_KEY = 'unpaginatedUnpaidPermits';
    protected $dto = GetListByIrhpApplicationUnpaged::class;
    protected $paramsMap = [
        'id' => 'irhpApplication',
    ];
    protected $defaultParamData = [
        'sort' => 'id',
        'order' => 'ASC',
    ];
}
