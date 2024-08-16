<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\QuestionAnswer as QuestionAnswerQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Retrieve a question/answer data for a given IRHP application
 */
final class QuestionAnswer extends AbstractQueryHandler
{
    protected $repoServiceName = 'IrhpApplication';

    /**
     * Handle query
     *
     * @param QueryInterface|QuestionAnswerQuery $query query
     *
     * @return Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->getRepo()->fetchUsingId($query);

        return $irhpApplication->getQuestionAnswerData();
    }
}
