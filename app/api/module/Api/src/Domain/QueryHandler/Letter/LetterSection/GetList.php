<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterSections
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterSection';
    
    protected $bundle = [
        'currentVersion'
    ];
}