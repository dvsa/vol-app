<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\MaxPermittedReachedByTypeAndOrganisation
    as MaxPermittedReachedByTypeAndOrganisationDto;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * Max permitted reached for type data source config
 */
class MaxPermittedReachedForType extends AbstractDataSource
{
    const DATA_KEY = 'maxPermittedReached';
    protected $dto = MaxPermittedReachedByTypeAndOrganisationDto::class;

    protected $paramsMap = [
        'type' => 'irhpPermitType',
        'organisation' => 'organisation',
    ];
}
