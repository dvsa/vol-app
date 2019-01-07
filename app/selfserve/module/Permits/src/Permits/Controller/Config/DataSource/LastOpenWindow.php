<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\LastOpenWindow as LastOpenWindowDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;
use DateTime;

/**
 * Open windows data source config
 */
class LastOpenWindow extends AbstractDataSource
{
    const DATA_KEY = 'lastOpenWindow';
    protected $dto = LastOpenWindowDto::class;

    public function __construct()
    {
        $currentDateTime = new DateTime();
        $this->extraQueryData['currentDateTime'] = $currentDateTime->format('Y-m-d H:i:s');
    }
}
