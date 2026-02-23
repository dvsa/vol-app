<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterIssue by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterIssue';
    
    protected $bundle = [
        'currentVersion' => [
            'category',
            'subCategory',
            'goodsOrPsv',
            'letterIssueType',
        ],
    ];
}
