<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterIssueTypes
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterIssueType';
}
