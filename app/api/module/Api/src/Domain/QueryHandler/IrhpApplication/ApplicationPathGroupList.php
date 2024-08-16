<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Application path group list
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ApplicationPathGroupList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'ApplicationPathGroup';
}
