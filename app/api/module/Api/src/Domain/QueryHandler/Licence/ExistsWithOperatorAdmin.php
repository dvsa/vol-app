<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Licence\ExistsWithOperatorAdmin as ExistsWithOperatorAdminQry;

class ExistsWithOperatorAdmin extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var LicenceRepo $repo
         * @var ExistsWithOperatorAdminQry $query
         */
        $repo = $this->getRepo();

        try {
            $licence = $repo->fetchByLicNoWithoutAdditionalData($query->getLicNo());
            $licenceExists = true;
            $hasOperatorAdmin = $licence->getOrganisation()->hasOperatorAdmin();
        } catch (NotFoundException) {
            $licenceExists = false;
            $hasOperatorAdmin = false;
        }

        return [
            'licenceExists' => $licenceExists,
            'hasOperatorAdmin' => $hasOperatorAdmin,
        ];
    }
}
