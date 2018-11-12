<?php

namespace Olcs\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceDto;

class Licence extends AbstractDataSource
{
    const DATA_KEY = 'licence';
    protected $dto = LicenceDto::class;
    protected $paramsMap = [
        'licence' => 'id'
    ];
}