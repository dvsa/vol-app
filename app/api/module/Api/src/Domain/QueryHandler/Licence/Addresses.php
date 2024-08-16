<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Addresses
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class Addresses extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    /**
     * Process query
     *
     * @param TransferQry\Licence\Addresses $query Query
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var \Dvsa\Olcs\Api\Domain\Repository\Licence $licenceRepo */
        $licenceRepo = $this->getRepo();

        return $this->result(
            $licenceRepo->fetchWithAddressesUsingId($query),
            [
                'correspondenceCd' => [
                    'address' => [
                        'countryCode',
                    ],
                    'contactType',
                    'phoneContacts' => [
                        'phoneContactType',
                    ],
                ],
                'establishmentCd' => [
                    'address' => [
                        'countryCode',
                    ],
                    'contactType',
                ],
                'transportConsultantCd' => [
                    'address' => [
                        'countryCode',
                    ],
                    'contactType',
                    'phoneContacts' => [
                        'phoneContactType',
                    ],
                ],
            ]
        );
    }
}
