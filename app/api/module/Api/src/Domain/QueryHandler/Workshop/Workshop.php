<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Workshop;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as WorkshopEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Workshop extends AbstractQueryHandler
{
    public const BUNDLE = [
        'contactDetails' => [
            'address' => ['countryCode']
        ]
    ];
    public const ERR_APP_MISMATCH = 'Workshop does not belong to application';
    public const ERR_LICENCE_MISMATCH = 'Workshop does not belong to licence';

    protected $repoServiceName = 'Workshop';

    public function handleQuery(QueryInterface $query)
    {
        /** @var WorkshopEntity $workshop */
        $workshop = $this->getRepo()->fetchUsingId($query);
        $queryLicenceId = $query->getLicence();
        $queryAppId = $query->getApplication();

        if (($queryLicenceId !== null && $queryLicenceId != $workshop->getLicence()->getId())) {
            throw new BadRequestException(self::ERR_LICENCE_MISMATCH);
        }

        if ($queryAppId !== null && !$workshop->getLicence()->isRelatedToApplication($queryAppId)) {
            throw new BadRequestException(self::ERR_APP_MISMATCH);
        }

        return $this->result($workshop, self::BUNDLE);
    }
}
