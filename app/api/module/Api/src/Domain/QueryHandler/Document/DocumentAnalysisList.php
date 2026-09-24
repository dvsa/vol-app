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
        $rows = $this->getRepo()->fetchAnalyses($query);

        return [
            'analyses' => array_map(
                static fn(DocumentAnalysis $row) => [
                    'id'          => $row->getId(),
                    'documentId'  => $row->getDocument()->getId(),
                    // Carried on the analysis so callers need no separate, paged document lookup.
                    'documentDate' => $row->getDocument()->getIssuedDate(true)?->format('Y-m-d H:i:s'),
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
