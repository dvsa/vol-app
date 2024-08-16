<?php

/**
 * Retrieve a Presiding TC by id
 *
 * @author Andy Newton <andy.newton@dvsa.gov.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'PresidingTc';
    protected $bundle = [
        'user'
        ];
}
