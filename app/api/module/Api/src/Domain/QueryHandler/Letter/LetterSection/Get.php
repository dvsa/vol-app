<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterSection by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterSection';
    
    protected $bundle = [
        'currentVersion'
    ];
}