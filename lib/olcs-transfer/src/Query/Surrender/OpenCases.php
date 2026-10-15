<?php

namespace Dvsa\Olcs\Transfer\Query\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * @package Dvsa\Olcs\Transfer\Query\Surrender
 */
#[Transfer\RouteName('backend/licence/single/surrender/open-cases')]
#[Transfer\Method('GET')]
class OpenCases extends AbstractQuery
{
    use Identity;
}
