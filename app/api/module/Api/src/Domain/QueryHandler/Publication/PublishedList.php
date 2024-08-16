<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\Publication\PublishedList as PublishedListCommand;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;

/**
 * List of published publications
 */
final class PublishedList extends AbstractQueryHandler
{
    protected $repoServiceName = 'Publication';


    /**
     * Handle PublishedList query
     *
     * @param QueryInterface|PublishedListCommand $query the query
     *
     * @return array
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var PublicationRepo $repo */
        $repo = $this->getRepo();
        $result = $repo->fetchPublishedList(
            $query,
            $query->getPubType(),
            $query->getPubDateFrom(),
            $query->getPubDateTo(),
            $query->getTrafficArea()
        );

        return [
            'result' => $this->resultList(
                $result['results'],
                [
                    'trafficArea',
                    'document'
                ]
            ),
            'count' => $result['count']
        ];
    }
}
