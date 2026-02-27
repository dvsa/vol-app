<?php

/**
 * Licence Registered Address
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Licence Registered Address
 *
 * @package Dvsa\Olcs\Api\Domain\QueryHandler\Licence
 */
class LicenceRegisteredAddress extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchForUserRegistration($query->getLicenceNumber()),
            [
                'correspondenceCd' => [
                    'address'
                ],
                'organisation'
            ]
        );
    }
}
