<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\OpenWindows as OpenWindowsDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Open windows data source config
 */
class OpenWindows extends AbstractDataSource
{
    const DATA_KEY = 'windows';
    protected $dto = OpenWindowsDto::class;
    protected $paramsMap = ['type' => 'permitType'];
}
