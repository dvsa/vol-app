<?php

namespace Olcs\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Licence\Licence as Licence;

class Licence extends AbstractDataSource
{
    const DATA_KEY = 'licence';
    protected $dto = Licence::class;
}