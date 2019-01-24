<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\OpenWindows as OpenWindowsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;
use DateTime;

/**
 * Open windows data source config
 */
class OpenWindows extends AbstractDataSource
{
    const DATA_KEY = 'windows';
    protected $dto = OpenWindowsDto::class;
    protected $paramsMap = ['type' => 'permitType'];

    public function __construct()
    {
        $currentDateTime = new DateTime();
        $this->extraQueryData['currentDateTime'] = $currentDateTime->format('Y-m-d H:i:s');
    }
}
