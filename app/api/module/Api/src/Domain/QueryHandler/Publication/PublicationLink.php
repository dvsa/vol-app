<?php

/**
 * PublicationLink
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;

/**
 * PublicationLink
 */
class PublicationLink extends AbstractQueryHandler
{
    protected $repoServiceName = 'PublicationLink';

    public function handleQuery(QueryInterface $query)
    {
        /** @var PublicationLinkEntity $publicationLink */
        $publicationLink = $this->getRepo()->fetchUsingId($query);
        $extraValues = ['isNew' => $publicationLink->getPublication()->isNew()];

        return $this->result(
            $publicationLink,
            [
                'publicationSection',
                'publication' => [
                    'pubStatus',
                    'trafficArea'
                ]
            ],
            $extraValues
        );
    }
}
