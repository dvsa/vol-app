<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Statement;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Entity\Cases\Statement as StatementEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Statement
 */
final class Statement extends AbstractQueryHandler
{
    protected $repoServiceName = 'Statement';

    /**
     * @param QueryInterface $query query
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var StatementEntity $statement */
        $statement = $this->getRepo()->fetchUsingId($query);
        $assignedCaseworker = $statement->getAssignedCaseworker();
        return $this->result(
            $statement,
            [
                'case',
                'requestorsContactDetails' => [
                    'address' => [
                        'countryCode'
                    ],
                    'contactType',
                    'person'
                ]
            ],
            [
                'assignedCaseworker' => $assignedCaseworker ? ['id' => $assignedCaseworker->getId()] : null,
            ]
        );
    }
}
