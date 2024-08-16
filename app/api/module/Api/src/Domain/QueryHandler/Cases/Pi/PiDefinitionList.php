<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Pi Definition List QueryHandler
 */
final class PiDefinitionList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'PiDefinition';
}
