<?php

namespace Olcs\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Surrender\ByLicence as SurrenderQuery;

class Surrender extends AbstractDataSource
{
    const DATA_KEY = 'surrender';
    protected $dto = SurrenderQuery::class;
    protected $paramsMap = [
        'licence' => 'id'
    ];
}
