<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmission as EbsrSubmissionQry;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;

/**
 * EbsrSubmission
 */
class EbsrSubmission extends AbstractQueryHandler
{
    protected $repoServiceName = 'EbsrSubmission';

    /**
     * Fetches a single EBSR submission record
     *
     * @param QueryInterface|EbsrSubmissionQry $query the query
     *
     * @return Result
     * @throws NotFoundException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var EbsrSubmissionRepo $repo */
        $repo = $this->getRepo();
        $ebsrSubmission = $repo->fetchUsingId($query);

        return $this->result(
            $ebsrSubmission,
            [
                'busReg' => [
                    'status'
                ],
                'document'
            ]
        );
    }
}
