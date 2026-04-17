<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterIssues
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterIssue';

    protected $bundle = [
        'currentVersion' => [
            'category',
            'subCategory',
            'goodsOrPsv',
            'letterIssueType'
        ]
    ];
}
