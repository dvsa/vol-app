<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Opposition;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Opposition
 */
final class Opposition extends AbstractQueryHandler
{
    protected $repoServiceName = 'Opposition';

    public function handleQuery(QueryInterface $query)
    {
        return $this->result(
            $this->getRepo()->fetchUsingId($query),
            [
                'case' => [
                    'application'
                ],
                'opposer' => [
                    'opposerType',
                    'contactDetails' => [
                        'address' => ['countryCode'],
                        'person',
                        'phoneContacts' => ['phoneContactType']
                    ]
                ],
                'grounds',
                'operatingCentres',
            ]
        );
    }
}
