<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterType by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterType';
    
    protected $bundle = [
        'masterTemplate',
        'sections',
        'issues',
        'todos',
        'appendices'
    ];
}