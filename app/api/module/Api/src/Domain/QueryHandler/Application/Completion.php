<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Retrieve an application alongside the completion status
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Completion extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'Application';
    protected $bundle = ['applicationCompletion'];
}
