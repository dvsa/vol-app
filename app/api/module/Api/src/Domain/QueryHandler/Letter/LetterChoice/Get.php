<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterChoice by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterChoice';
}
