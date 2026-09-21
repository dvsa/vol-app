<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class DocumentAnalysisList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocumentAnalysis';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        //throw new \RuntimeException('APP: ' . var_export($query->getApplication(), true));
        $rows = $this->getRepo()->fetchAnalyses($query);

        return [
            'analyses' => array_map(
                static fn(DocumentAnalysis $row) => [
                    'id'          => $row->getId(),
                    'documentId'  => $row->getDocument()->getId(),
                    'status'      => $row->getStatus(),
                    'result'      => $row->getResult(),
                    'metadata'    => $row->getResultMetadata(),
                    'errorDetail' => $row->getErrorDetail(),
                    'completedAt' => $row->getCompletedAt(true)?->format('Y-m-d H:i:s'),
                ],
                $rows
            ),
        ];
    }
}
