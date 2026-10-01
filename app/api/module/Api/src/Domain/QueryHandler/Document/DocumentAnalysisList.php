<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Document;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Psr\Container\ContainerInterface;

/**
 * One page of document analyses plus the total, through the repository's standard paged and
 * ordered list like DocumentList. The rows are mapped by hand rather than bundle-serialised:
 * the entity carries the raw analysis token, which must never leave the API, and callers want
 * the document's issued date flattened onto each row.
 *
 * The raw result is deliberately not returned. It holds the applicant profile sent to the model
 * and the pipeline's provenance (bucket, key, execution ARN); callers render the normalised
 * payload, which carries neither.
 */
class DocumentAnalysisList extends AbstractQueryHandler
{
    protected $repoServiceName = 'DocumentAnalysis';

    private AnalysisResultNormaliser $normaliser;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->normaliser = $container->get(AnalysisResultNormaliser::class);

        return parent::__invoke($container, $requestedName, $options);
    }

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
                fn(DocumentAnalysis $row) => [
                    'id'          => $row->getId(),
                    'documentId'  => $row->getDocument()->getId(),
                    // Carried on the analysis so callers need no separate, paged document lookup:
                    // the date labels the tab, the description and filename label the link to the file.
                    'documentDescription' => $row->getDocument()->getDescription(),
                    'documentFilename' => $row->getDocument()->getFilename(),
                    'documentDate' => $row->getDocument()->getIssuedDate(true)?->format('Y-m-d H:i:s'),
                    'status'      => $row->getStatus(),
                    // Always at the current payload version, whatever version the row was written
                    // with. Null when the stored report held no analysis that could be normalised.
                    'resultNormalised' => $this->normaliser->fromStored($row->getResultNormalised())?->toArray(),
                    'errorDetail' => $row->getErrorDetail(),
                    'completedAt' => $row->getCompletedAt(true)?->format('Y-m-d H:i:s'),
                ],
                $rows
            ),
            'count' => $repo->fetchCount($query),
        ];
    }
}
