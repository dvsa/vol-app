<?php

/**
 * SubmissionSectionComment
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Submission;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * SubmissionSectionComment
 */
class SubmissionSectionComment extends AbstractQueryHandler
{
    protected $repoServiceName = 'SubmissionSectionComment';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query)
        );
    }
}
