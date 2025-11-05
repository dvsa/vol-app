<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterIssueType by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterIssueType';
}
