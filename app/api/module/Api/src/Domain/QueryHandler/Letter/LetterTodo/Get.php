<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterTodo by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterTodo';
    
    protected $bundle = [
        'currentVersion'
    ];
}
