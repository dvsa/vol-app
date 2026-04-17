<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterTestData;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterTestData by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterTestData';
}
