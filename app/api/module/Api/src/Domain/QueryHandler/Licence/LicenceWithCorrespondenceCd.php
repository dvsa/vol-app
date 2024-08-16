<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity;

class LicenceWithCorrespondenceCd extends AbstractQueryHandler
{
    protected $repoServiceName = 'Licence';

    /**
     * Handle query
     *
     * @param QueryInterface $query DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryHandler\Result
     */
    public function handleQuery(QueryInterface $query)
    {
        /** @var Entity\Licence\Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($query);
        $tradingNames = $this->resultList($licence->getTradingNames());
        return $this->result(
            $licence,
            [
                'correspondenceCd' => [
                    'address' => [
                        'countryCode',
                    ],
                    'phoneContacts' => [
                        'phoneContactType',
                    ]
                ],
                'organisation'
            ],
            [
                'organisation' => [
                    'tradingNames' => $tradingNames
                ]
            ]
        );
    }
}
