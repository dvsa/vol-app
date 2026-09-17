<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class DocumentAnalysisList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocumentAnalysis';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        $rows = $this->getRepo()->fetchByApplicationId((int) $query->getApplication());

        return $this->result(
            null,
            [],
            [
                'analyses' => array_map(
                    static fn($row) => [
                        'id'          => $row->getId(),
                        'documentId'  => $row->getDocument()?->getId(),
                        'status'      => $row->getStatus(),
                        'result'      => $row->getResult(),
                        'metadata'    => $row->getResultMetadata(),
                        'errorDetail' => $row->getErrorDetail(),
                        'completedAt' => $row->getCompletedAt(true)?->format('Y-m-d H:i:s'),
                    ],
                    $rows
                ),
            ]
        );
    }
}
