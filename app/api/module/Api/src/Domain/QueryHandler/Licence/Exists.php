<?php

/**
 * Exists
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Licence\Exists as LicenceExistsQry;

/**
 * Class Exists
 *
 * @package Dvsa\Olcs\Api\Domain\QueryHandler\Licence
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Exists extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    public function handleQuery(QueryInterface $query)
    {
        /**
         * @var LicenceRepo $repo
         * @var LicenceExistsQry $query
         */
        $repo = $this->getRepo();
        return ['isValid' => $repo->existsByLicNo($query->getLicNo())];
    }
}
