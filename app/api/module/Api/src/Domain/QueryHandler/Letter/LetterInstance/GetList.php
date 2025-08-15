<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterInstances
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterInstance';
}
