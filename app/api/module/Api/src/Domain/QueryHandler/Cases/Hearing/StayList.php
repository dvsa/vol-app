<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Hearing;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Stay List Query Handler
 */
final class StayList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Stay';

    protected $bundle = ['case'];
}
