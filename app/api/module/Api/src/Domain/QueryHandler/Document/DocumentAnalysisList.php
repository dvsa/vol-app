<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * One page of document analyses plus the total, through the repository's standard paged and
 * ordered list like DocumentList. The rows are mapped by hand rather than bundle-serialised:
 * the entity carries the raw analysis token, which must never leave the API, and callers want
 * the document's issued date flattened onto each row.
 */
class DocumentAnalysisList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocumentAnalysis';

    /**
     * @param \Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList $query
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /** @var DocumentAnalysisRepo $repo */
        $repo = $this->getRepo();

        // fetchList() yields the paginator's iterator, not an array.
        $rows = iterator_to_array($repo->fetchList($query, Query::HYDRATE_OBJECT), false);

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
            'count' => $repo->fetchCount($query),
        ];
    }
}
