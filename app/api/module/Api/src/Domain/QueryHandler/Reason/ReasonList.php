<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Reason;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Reason List QueryHandler
 */
final class ReasonList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Reason';
}
